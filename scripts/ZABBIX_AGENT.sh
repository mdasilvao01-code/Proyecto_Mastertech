#!/bin/bash
# =============================================================================
# ZABBIX AGENT - Para instalar en TODAS las VMs existentes
# Uso: bash ZABBIX_AGENT.sh <hostname> <ip_propia>
# Ejemplo: bash ZABBIX_AGENT.sh web1 192.168.20.12
# =============================================================================
set -e
export DEBIAN_FRONTEND=noninteractive

HOSTNAME=${1:-$(hostname)}
MY_IP=${2:-$(hostname -I | awk '{print $2}')}
ZABBIX_SERVER="192.168.10.30"

echo "Instalando Zabbix Agent en $HOSTNAME ($MY_IP)..."

apt-get update -qq
wget -q https://repo.zabbix.com/zabbix/6.4/debian/pool/main/z/zabbix-release/zabbix-release_6.4-1+debian12_all.deb \
    -O /tmp/zabbix-release.deb
dpkg -i /tmp/zabbix-release.deb
apt-get update -qq
apt-get install -y zabbix-agent

cat > /etc/zabbix/zabbix_agentd.conf << EOF
PidFile=/run/zabbix/zabbix_agentd.pid
LogFile=/var/log/zabbix/zabbix_agentd.log
LogFileSize=0
Server=$ZABBIX_SERVER
ServerActive=$ZABBIX_SERVER
Hostname=$HOSTNAME.mastertech.local
Include=/etc/zabbix/zabbix_agentd.d/*.conf
EOF

systemctl enable zabbix-agent
systemctl restart zabbix-agent
systemctl is-active zabbix-agent && echo "✅ Zabbix Agent activo en $HOSTNAME" || echo "❌ Error"