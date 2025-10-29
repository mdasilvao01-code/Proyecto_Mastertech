#!/bin/bash
apt update && apt install -y apache2

echo "<h1>Bienvenido a Mastertech</h1>" > /var/www/html/index.html

systemctl restart apache2