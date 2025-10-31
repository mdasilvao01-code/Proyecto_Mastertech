#!/bin/bash

export DEBIAN_FRONTEND=noninteractive

### DHCP CONFIGURACIÓN ###

# Instalar servidor DHCP
apt update
apt install -y isc-dhcp-server

# Configurar interfaz de escucha
echo 'INTERFACESv4="eth1"' > /etc/default/isc-dhcp-server

# Crear archivo de configuración DHCP
cat <<EOF > /etc/dhcp/dhcpd.conf
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet 192.168.10.0 netmask 255.255.255.0 {
  range 192.168.10.100 192.168.10.200;
  option routers 192.168.10.1;
  option domain-name-servers 192.168.10.10;
  option domain-name "Mastertech.local";
}
EOF

# Forzar configuración y reiniciar
dpkg --configure -a
systemctl restart isc-dhcp-server

### DNS CONFIGURACIÓN ###

# Instalar servidor DNS
apt install -y bind9

# Crear archivo de zonas
cat <<EOF > /etc/bind/named.conf.local
zone "Mastertech.local" {
  type master;
  file "/etc/bind/db.Mastertech.local";
};

zone "10.168.192.in-addr.arpa" {
  type master;
  file "/etc/bind/db.192";
};
EOF

# Crear zona directa
cat <<EOF > /etc/bind/db.Mastertech.local
\$TTL    604800
@       IN      SOA     Mastertech.local. root.Mastertech.local. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      infra.Mastertech.local.
infra   IN      A       192.168.10.10
ftp     IN      A       192.168.10.11
EOF

# Crear zona reversa
cat <<EOF > /etc/bind/db.192
\$TTL    604800
@       IN      SOA     Mastertech.local. root.Mastertech.local. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      infra.Mastertech.local.
10      IN      PTR     infra.Mastertech.local.
11      IN      PTR     ftp.Mastertech.local.
EOF

# Reiniciar servicio DNS
systemctl restart bind9

### FTP CONFIGURACIÓN ###

# Instalar servidor FTP
apt install -y vsftpd

# Crear estructura de directorios
mkdir -p /ftp/shared
mkdir -p /ftp/users/dev1
mkdir -p /ftp/users/dev2

# Crear usuarios
useradd -m dev1
useradd -m dev2
echo "dev1:abcd" | chpasswd
echo "dev2:abcd" | chpasswd

# Asignar permisos
chown dev1 /ftp/users/dev1
chown dev2 /ftp/users/dev2

# Reiniciar servicio FTP
systemctl restart vsftpd