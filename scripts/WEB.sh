#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando Apache2 + NFS en servidor web..."

echo "nameserver 8.8.8.8" > /etc/resolv.conf

apt-get update -y
apt-get install -y apache2 php libapache2-mod-php \
  php-mysqli php-pdo php-mysql php-mbstring php-xml \
  nfs-common

# Crear punto de montaje y montar NFS desde RED SERVICIO (192.168.40.12)
mkdir -p /mnt/nfs_html
mount 192.168.40.12:/var/www/html /mnt/nfs_html

if ! grep -q "192.168.40.12:/var/www/html" /etc/fstab; then
  echo "192.168.40.12:/var/www/html /mnt/nfs_html nfs defaults,_netdev 0 0" >> /etc/fstab
fi

# Cambiar DocumentRoot al NFS
sed -i 's|/var/www/html|/mnt/nfs_html|g' /etc/apache2/sites-available/000-default.conf

cat <<EOF >> /etc/apache2/apache2.conf

<Directory /mnt/nfs_html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF

a2enmod rewrite
systemctl enable apache2
systemctl restart apache2

echo "✅ Apache2 montando NFS desde 192.168.40.12 (red servicio)"