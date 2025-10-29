#!/bin/bash
apt update && apt install -y vsftpd

mkdir -p /ftp/shared
mkdir -p /ftp/users/dev1
mkdir -p /ftp/users/dev2

useradd -m dev1
useradd -m dev2
echo "dev1:abcd" | chpasswd
echo "dev2:abcd" | chpasswd

chown dev1 /ftp/users/dev1
chown dev2 /ftp/users/dev2

systemctl restart vsftpd