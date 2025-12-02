#!/bin/bash
set -e

apt-get update -y
apt-get install -y haproxy

cat <<EOF > /etc/haproxy/haproxy.cfg
global
    log /dev/log local0
    maxconn 4096
    user haproxy
    group haproxy

defaults
    log     global
    mode    http
    option  httplog
    option  dontlognull
    timeout connect 5000ms
    timeout client  50000ms
    timeout server  50000ms

frontend http_front
    bind *:80
    default_backend web_servers

backend web_servers
    balance roundrobin
    server web1 192.168.10.12:80 check
    server web2 192.168.10.15:80 check
EOF

systemctl enable haproxy
systemctl restart haproxy