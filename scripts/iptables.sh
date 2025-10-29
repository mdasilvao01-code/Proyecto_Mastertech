#!/bin/bash
# Politica por defecto
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT

# Permitir loopback
iptables -A INPUT -i lo -j ACCEPT

# Permitir SSH solo desde IP autorizada
iptables -A INPUT -p tcp --dport 22 -s 192.168.10.1 -j ACCEPT

# Permitir acceso a la base de datos solo desde el servidor web
iptables -A INPUT -p tcp --dport 3306 -s 192.168.10.12 -j ACCEPT

# Permitir trafico FTP interno
iptables -A INPUT -p tcp --dport 21 -s 192.168.10.0/24 -j ACCEPT

# Registrar intentos bloqueados
iptables -A INPUT -j LOG --log-prefix "IPTABLES DROP: "

# Guardar reglas
iptables-save > /etc/iptables.rules