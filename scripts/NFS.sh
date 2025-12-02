#!/bin/bash
set -e

# Instalar NFS server
apt-get update -y
apt-get install -y nfs-kernel-server

# Crear carpeta compartida
mkdir -p /var/www/html
chown -R www-data:www-data /var/www/html

# Añadir export solo si no existe ya
if ! grep -q "/var/www/html 192.168.10.0/24" /etc/exports; then
  echo "/var/www/html 192.168.10.0/24(rw,sync,no_subtree_check,no_root_squash)" >> /etc/exports
fi

# Aplicar configuración
exportfs -ra

systemctl enable nfs-kernel-server
systemctl restart nfs-kernel-server

# Copiar (si existe) contenido desde /vagrant/php_app
if [ -d /vagrant/php_app ]; then
  cp -r /vagrant/php_app/* /var/www/html/ || true
fi
