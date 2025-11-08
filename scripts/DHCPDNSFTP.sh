#!/bin/bash

set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando DHCP, DNS y FTP en la máquina infra..."

### DHCP CONFIGURACIÓN ###
apt update
apt install -y isc-dhcp-server

echo 'INTERFACESv4="eth2"' > /etc/default/isc-dhcp-server

cat <<EOF > /etc/dhcp/dhcpd.conf
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet 192.168.10.0 netmask 255.255.255.0 {
  range 192.168.10.100 192.168.10.200;
  option routers 192.168.10.1;
  option domain-name-servers 192.168.10.10;
  option domain-name "mastertech.lan";
}
EOF

systemctl restart isc-dhcp-server

### DNS CONFIGURACIÓN ###
apt install -y bind9

cat <<EOF > /etc/bind/named.conf.local
zone "mastertech.lan" {
  type master;
  file "/etc/bind/db.mastertech.lan";
};

zone "10.168.192.in-addr.arpa" {
  type master;
  file "/etc/bind/db.192.168.10";
};
EOF

cat <<EOF > /etc/bind/db.mastertech.lan
\$TTL    604800
@       IN      SOA     mastertech.lan. root.mastertech.lan. (
                        $(date +%Y%m%d%H) ; Serial
                        604800     ; Refresh
                        86400      ; Retry
                        2419200    ; Expire
                        604800 )   ; Negative Cache TTL
;
@       IN      NS      infra.mastertech.lan.
infra   IN      A       192.168.10.10
ftp     IN      A       192.168.10.11
web     IN      A       192.168.10.12
mastertech IN   A       192.168.10.12
EOF

cat <<EOF > /etc/bind/db.192.168.10
\$TTL    604800
@       IN      SOA     mastertech.lan. root.mastertech.lan. (
                        $(date +%Y%m%d%H) ; Serial
                        604800     ; Refresh
                        86400      ; Retry
                        2419200    ; Expire
                        604800 )   ; Negative Cache TTL
;
@       IN      NS      infra.mastertech.lan.
10      IN      PTR     infra.mastertech.lan.
11      IN      PTR     ftp.mastertech.lan.
12      IN      PTR     web.mastertech.lan.
EOF

named-checkconf
named-checkzone mastertech.lan /etc/bind/db.mastertech.lan
named-checkzone 10.168.192.in-addr.arpa /etc/bind/db.192.168.10

systemctl restart bind9

### FTP CONFIGURACIÓN ###
apt install -y vsftpd

mkdir -p /ftp/shared /ftp/users/dev1 /ftp/users/dev2

useradd -m dev1 || true
useradd -m dev2 || true
echo "dev1:abcd" | chpasswd
echo "dev2:abcd" | chpasswd

chown dev1 /ftp/users/dev1
chown dev2 /ftp/users/dev2

systemctl restart vsftpd

echo "✅ Infraestructura lista en la máquina infra: DHCP, DNS y FTP funcionando"