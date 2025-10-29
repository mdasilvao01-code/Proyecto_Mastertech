#!/bin/bash
apt update && apt install -y bind9

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

cp /etc/bind/db.local /etc/bind/db.Mastertech.local
cp /etc/bind/db.127 /etc/bind/db.192

# Edita los archivos db.Mastertech.local y db.192 para incluir tus hosts

systemctl restart bind9