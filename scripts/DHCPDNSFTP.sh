#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando DHCP, DNS y FTP en infra (192.168.10.10)..."

# Forzar DNS para apt
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 1.1.1.1" >> /etc/resolv.conf

apt-get update -y

# ============================================================================
# DHCP - solo RED EMPRESA 192.168.10.0/24
# eth1 = 192.168.10.10 (segunda NIC, primera es NAT eth0)
# ============================================================================
apt-get install -y isc-dhcp-server

echo 'INTERFACESv4="eth1"' > /etc/default/isc-dhcp-server

cat <<EOF > /etc/dhcp/dhcpd.conf
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet 192.168.10.0 netmask 255.255.255.0 {
  range 192.168.10.100 192.168.10.200;
  option routers 192.168.10.1;
  option domain-name-servers 192.168.10.10;
  option domain-name "mastertech.local";
}
EOF

systemctl enable isc-dhcp-server
systemctl restart isc-dhcp-server || true

# ============================================================================
# DNS - bind9 con todas las redes
# ============================================================================
apt-get install -y bind9 bind9utils

cat <<EOF > /etc/bind/named.conf.local
zone "mastertech.local" {
  type master;
  file "/etc/bind/db.mastertech.local";
};

zone "10.168.192.in-addr.arpa" {
  type master;
  file "/etc/bind/db.192.168.10";
};

zone "20.168.192.in-addr.arpa" {
  type master;
  file "/etc/bind/db.192.168.20";
};

zone "40.168.192.in-addr.arpa" {
  type master;
  file "/etc/bind/db.192.168.40";
};
EOF

cat <<EOF > /etc/bind/db.mastertech.local
\$TTL    604800
@       IN      SOA     mastertech.local. root.mastertech.local. (
                        $(date +%Y%m%d%H) ; Serial
                        604800 ; Refresh
                        86400  ; Retry
                        2419200 ; Expire
                        604800 ) ; Negative Cache TTL
;
@       IN      NS      infra.mastertech.local.
; RED EMPRESA 192.168.10.x
infra   IN      A       192.168.10.10
router  IN      A       192.168.10.1
; RED DMZ 192.168.20.x
lb      IN      A       192.168.20.16
web1    IN      A       192.168.20.12
web2    IN      A       192.168.20.15
mastertech IN   A       192.168.20.16
; RED SERVICIO 192.168.40.x
web1srv IN      A       192.168.40.10
web2srv IN      A       192.168.40.11
nfs     IN      A       192.168.40.12
db      IN      A       192.168.40.13
EOF

cat <<EOF > /etc/bind/db.192.168.10
\$TTL 604800
@ IN SOA mastertech.local. root.mastertech.local. (
  $(date +%Y%m%d%H) 604800 86400 2419200 604800 )
@ IN NS infra.mastertech.local.
1  IN PTR router.mastertech.local.
10 IN PTR infra.mastertech.local.
EOF

cat <<EOF > /etc/bind/db.192.168.20
\$TTL 604800
@ IN SOA mastertech.local. root.mastertech.local. (
  $(date +%Y%m%d%H) 604800 86400 2419200 604800 )
@ IN NS infra.mastertech.local.
12 IN PTR web1.mastertech.local.
15 IN PTR web2.mastertech.local.
16 IN PTR lb.mastertech.local.
EOF

cat <<EOF > /etc/bind/db.192.168.40
\$TTL 604800
@ IN SOA mastertech.local. root.mastertech.local. (
  $(date +%Y%m%d%H) 604800 86400 2419200 604800 )
@ IN NS infra.mastertech.local.
10 IN PTR web1srv.mastertech.local.
11 IN PTR web2srv.mastertech.local.
12 IN PTR nfs.mastertech.local.
13 IN PTR db.mastertech.local.
EOF

named-checkconf
named-checkzone mastertech.local /etc/bind/db.mastertech.local
systemctl enable named
systemctl restart named

# ============================================================================
# FTP - vsftpd
# ============================================================================
apt-get install -y vsftpd

useradd -m dev1 2>/dev/null || true
useradd -m dev2 2>/dev/null || true
echo "dev1:abcd" | chpasswd
echo "dev2:abcd" | chpasswd

systemctl enable vsftpd
systemctl restart vsftpd

echo "✅ DHCP (192.168.10.x), DNS y FTP configurados en infra"