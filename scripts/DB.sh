#!/bin/bash
apt update
apt install -y mariadb-server

# Crear base de datos y usuario
mysql -u root <<EOF
CREATE DATABASE Mastertech;
sudo mysql -e "CREATE USER 'devweb'@'192.168.10.12' IDENTIFIED BY 'abcd';"
sudo mysql -e "GRANT ALL PRIVILEGES ON mastertech.* TO 'devweb'@'192.168.10.12';"
sudo mysql -e "FLUSH PRIVILEGES;"
FLUSH PRIVILEGES;
EOF

systemctl restart mariadb