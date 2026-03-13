#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando NFS en nfs (192.168.40.12)..."

echo "nameserver 8.8.8.8" > /etc/resolv.conf

apt-get update -y
apt-get install -y nfs-kernel-server

mkdir -p /var/www/html
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html

# Exportar solo a la RED SERVICIO 192.168.40.0/24
if ! grep -q "/var/www/html 192.168.40.0/24" /etc/exports; then
  echo "/var/www/html 192.168.40.0/24(rw,sync,no_subtree_check,no_root_squash)" >> /etc/exports
fi

exportfs -ra
systemctl enable nfs-kernel-server
systemctl restart nfs-kernel-server

# Copiar app desde /vagrant/html
if [ -d /vagrant/html ]; then
  cp -r /vagrant/html/. /var/www/html/
  chown -R www-data:www-data /var/www/html
  chmod -R 775 /var/www/html
  echo "✅ PHP copiado desde /vagrant/html/"
fi

echo "✅ NFS exportando /var/www/html a 192.168.40.0/24"