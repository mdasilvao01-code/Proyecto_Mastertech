#!/bin/bash
apt update
apt install -y mariadb-server

# Crear base de datos y usuario
mysql -u root <<EOF
CREATE DATABASE Mastertech;
CREATE USER 'Masterweb'@'192.168.10.12' IDENTIFIED BY 'abcd';
GRANT ALL PRIVILEGES ON Mastertech.* TO 'devweb'@'192.168.10.12';
FLUSH PRIVILEGES;
EOF

systemctl restart mariadb