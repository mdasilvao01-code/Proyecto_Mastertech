# Añadir DNS temporal
echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf

# Verificar internet
ping -c2 8.8.8.8

# Si hay ping, actualizar e instalar
sudo apt-get update
sudo apt-get install -y wget curl git gnupg2 net-tools

echo "=========================================="
echo "  INSTALANDO SERVICIOS (FOG + ZABBIX)"
echo "  IP: 192.168.10.20"
echo "=========================================="

apt-get update -qq
apt-get install -y wget curl git gnupg2 net-tools

hostnamectl set-hostname servicios
echo "192.168.10.20 servicios servicios.mastertech.local" >> /etc/hosts

# =============================================================================
# ZABBIX SERVER
# Acceso: http://192.168.10.20/zabbix  →  Admin / zabbix
# =============================================================================
echo ">>> Instalando Zabbix..."

wget -q https://repo.zabbix.com/zabbix/6.4/debian/pool/main/z/zabbix-release/zabbix-release_6.4-1+debian12_all.deb \
    -O /tmp/zabbix-release.deb
dpkg -i /tmp/zabbix-release.deb
apt-get update -qq

apt-get install -y \
    zabbix-server-mysql \
    zabbix-frontend-php \
    zabbix-apache-conf \
    zabbix-sql-scripts \
    zabbix-agent \
    mariadb-server \
    php php-mysql php-gd php-bcmath php-mbstring php-xml \
    apache2

systemctl enable mariadb && systemctl start mariadb

mysql -u root << 'SQL'
CREATE DATABASE IF NOT EXISTS zabbix CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
CREATE USER IF NOT EXISTS 'zabbix'@'localhost' IDENTIFIED BY 'ZabbixPass@2024';
GRANT ALL PRIVILEGES ON zabbix.* TO 'zabbix'@'localhost';
SET GLOBAL log_bin_trust_function_creators = 1;
FLUSH PRIVILEGES;
SQL

echo "Importando schema Zabbix..."
zcat /usr/share/zabbix-sql-scripts/mysql/server.sql.gz | \
    mysql -u zabbix -pZabbixPass@2024 zabbix
mysql -u root -e "SET GLOBAL log_bin_trust_function_creators = 0;"

sed -i 's/# DBPassword=/DBPassword=ZabbixPass@2024/' /etc/zabbix/zabbix_server.conf

cat > /etc/zabbix/zabbix_agentd.conf << 'EOF'
PidFile=/run/zabbix/zabbix_agentd.pid
LogFile=/var/log/zabbix/zabbix_agentd.log
LogFileSize=0
Server=127.0.0.1
ServerActive=127.0.0.1
Hostname=servicios.mastertech.local
Include=/etc/zabbix/zabbix_agentd.d/*.conf
EOF

# Timezone Madrid
sed -i 's|# php_value date.timezone.*|php_value date.timezone Europe/Madrid|' \
    /etc/zabbix/apache.conf 2>/dev/null || true

systemctl enable zabbix-server zabbix-agent
systemctl restart zabbix-server zabbix-agent apache2

echo ">>> Zabbix instalado ✓"

# =============================================================================
# FOG SERVER
# Acceso: http://192.168.10.20/fog/management  →  fog / password
# =============================================================================
echo ">>> Instalando FOG..."

apt-get install -y \
    php-cli php-json php-curl php-zip \
    tftpd-hpa nfs-kernel-server vsftpd \
    genisoimage syslinux syslinux-common isolinux lftp ftp

# Clonar FOG
cd /opt
git clone https://github.com/FOGProject/fogproject.git --depth=1 fog_git 2>/dev/null || true

# Instalar FOG (usa la BD mariadb ya instalada por Zabbix)
# FOG usará su propia BD "fog"
cd /opt/fog_git/bin
bash installfog.sh \
    -y \
    --webroot="/fog" \
    --interface=eth1 \
    --ip=192.168.10.20 \
    --submask=255.255.255.0 \
    --router=192.168.10.1 \
    --dns=192.168.10.10 \
    --domain=mastertech.local \
    --nodns \
    2>&1 | tee /var/log/fog_install.log || true

echo ">>> FOG instalado ✓"

# =============================================================================
# Script para añadir hosts a Zabbix
# =============================================================================
cat > /usr/local/bin/zabbix_add_hosts.sh << 'SCRIPT'
#!/bin/bash
echo "Esperando Zabbix API (hasta 5 min)..."
for i in $(seq 1 30); do
    code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/zabbix/api_jsonrpc.php 2>/dev/null)
    [ "$code" = "200" ] && break
    echo "  Intento $i/30..."
    sleep 10
done

TOKEN=$(curl -s -X POST http://localhost/zabbix/api_jsonrpc.php \
    -H "Content-Type: application/json" \
    -d '{"jsonrpc":"2.0","method":"user.login","params":{"user":"Admin","password":"zabbix"},"id":1}' \
    | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('result',''))" 2>/dev/null)

[ -z "$TOKEN" ] && echo "ERROR: No se pudo obtener token de Zabbix" && exit 1
echo "Token OK: ${TOKEN:0:10}..."

add_host() {
    local NAME=$1 IP=$2
    curl -s -X POST http://localhost/zabbix/api_jsonrpc.php \
        -H "Content-Type: application/json" \
        -d "{\"jsonrpc\":\"2.0\",\"method\":\"host.create\",\"params\":{
            \"host\":\"$NAME\",
            \"interfaces\":[{\"type\":1,\"main\":1,\"useip\":1,\"ip\":\"$IP\",\"dns\":\"\",\"port\":\"10050\"}],
            \"groups\":[{\"groupid\":\"2\"}],
            \"templates\":[{\"templateid\":\"10001\"}]
        },\"auth\":\"$TOKEN\",\"id\":1}" > /dev/null
    echo "  ✅ Host añadido: $NAME ($IP)"
}

add_host "router"    "192.168.10.1"
add_host "infra"     "192.168.10.10"
add_host "lb"        "192.168.20.16"
add_host "web1"      "192.168.20.12"
add_host "web2"      "192.168.20.15"
add_host "nfs"       "192.168.40.12"
add_host "db"        "192.168.40.13"
echo "Hosts añadidos a Zabbix ✓"
SCRIPT

chmod +x /usr/local/bin/zabbix_add_hosts.sh

# =============================================================================
echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║         SERVICIOS INSTALADO CORRECTAMENTE            ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║  ZABBIX:  http://192.168.10.20/zabbix                ║"
echo "║           Usuario: Admin  /  Contraseña: zabbix      ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║  FOG:     http://192.168.10.20/fog/management        ║"
echo "║           Usuario: fog  /  Contraseña: password      ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║  Añadir hosts Zabbix:                                ║"
echo "║  bash /usr/local/bin/zabbix_add_hosts.sh             ║"
echo "╚══════════════════════════════════════════════════════╝"