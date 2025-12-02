#!/bin/bash
set -e

# Instalar Apache, PHP y cliente NFS
apt-get update -y
apt-get install -y apache2 php libapache2-mod-php nfs-common

# Crear punto de montaje y montar NFS
mkdir -p /mnt/nfs_html
mount 192.168.10.14:/var/www/html /mnt/nfs_html

# Persistir montaje en /etc/fstab
if ! grep -q "192.168.10.14:/var/www/html" /etc/fstab; then
  echo "192.168.10.14:/var/www/html /mnt/nfs_html nfs defaults 0 0" >> /etc/fstab
fi

# Cambiar DocumentRoot de Apache al NFS
sed -i 's|/var/www/html|/mnt/nfs_html|g' /etc/apache2/sites-available/000-default.conf

# Crear archivo phpinfo en el NFS
cat << 'EOF' > /mnt/nfs_html/index.php
<?php
phpinfo();
?>
EOF

# Habilitar y reiniciar Apache
systemctl enable apache2
systemctl restart apache2
