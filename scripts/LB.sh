#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando HAProxy en lb (192.168.20.16)..."

echo "nameserver 8.8.8.8" > /etc/resolv.conf

apt-get update -y
apt-get install -y haproxy

cat <<EOF > /etc/haproxy/haproxy.cfg
global
    log /dev/log local0
    log /dev/log local1 notice
    maxconn 4096
    user haproxy
    group haproxy
    daemon

defaults
    log     global
    mode    http
    option  httplog
    option  dontlognull
    option  forwardfor
    option  http-server-close
    timeout connect 5000ms
    timeout client  50000ms
    timeout server  50000ms

frontend http_front
    bind *:80
    default_backend web_servers

backend web_servers
    balance roundrobin
    option httpchk GET /
    server web1 192.168.20.12:80 check
    server web2 192.168.20.15:80 check
EOF

systemctl enable haproxy
systemctl restart haproxy

echo "✅ HAProxy listo. Balanceando entre web1 (192.168.20.12) y web2 (192.168.20.15)"