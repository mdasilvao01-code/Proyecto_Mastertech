#!/usr/bin/env bash
set -euo pipefail

echo "=== Configurando Router/NAT + DHCP/DNS ==="

# 1. Forzar DNS en resolv.conf y evitar que systemd lo sobreescriba
rm -f /etc/resolv.conf
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 1.1.1.1" >> /etc/resolv.conf
chattr +i /etc/resolv.conf   # lo deja inmutable

# 2. Detectar interfaces
WAN_IF="$(ip -o -4 route show to default | awk '{print $5}' | head -n1)"
LAN_IF="$(ip -o link show | awk -F': ' '$2 ~ /enp0s8|eth1/ {print $2}' | head -n1)"

echo "WAN_IF=${WAN_IF:-unknown}, LAN_IF=${LAN_IF:-unknown}"

# 3. Activar IP forwarding
sysctl -w net.ipv4.ip_forward=1
echo 'net.ipv4.ip_forward=1' > /etc/sysctl.d/99-ipforward.conf

# 4. Instalar paquetes
apt-get update -y
apt-get install -y nftables dnsmasq

# 5. Configurar NAT
cat > /etc/nftables.conf <<EOF
#!/usr/sbin/nft -f
flush ruleset

table inet nat {
  chain postrouting {
    type nat hook postrouting priority 100;
    oifname "${WAN_IF}" ip saddr 192.168.10.0/24 counter masquerade
  }
}
EOF

systemctl enable nftables
systemctl restart nftables

# 6. Configurar dnsmasq
mv /etc/dnsmasq.conf /etc/dnsmasq.conf.orig || true
cat > /etc/dnsmasq.d/router.conf <<EOF
interface=${LAN_IF}
bind-interfaces
dhcp-range=192.168.10.100,192.168.10.200,255.255.255.0,12h
dhcp-option=3,192.168.10.1
dhcp-option=6,192.168.10.1
domain=Mastertech.local
expand-hosts
EOF

systemctl enable dnsmasq
systemctl restart dnsmasq

echo "=== Router configurado correctamente: WAN=${WAN_IF}, LAN=${LAN_IF} ==="