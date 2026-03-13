# -*- mode: ruby -*-
# vi: set ft=ruby :

# ============================================================================
# INFRAESTRUCTURA MASTERTECH - Topología completa
#
# RED EMPRESA (red1)  → 192.168.10.0/24
#   router  → 192.168.10.1
#   infra   → 192.168.10.10  (DHCP, DNS, FTP)
#
# RED DMZ (red2)      → 192.168.20.0/24
#   router  → 192.168.20.1
#   lb      → 192.168.20.16
#   web1    → 192.168.20.12
#   web2    → 192.168.20.15
#
# RED SERVICIO (red3) → 192.168.40.0/24
#   web1    → 192.168.40.10
#   web2    → 192.168.40.11
#   nfs     → 192.168.40.12
#   db      → 192.168.40.13
#
# ORDEN DE ARRANQUE:
#   router → infra → nfs → db → lb → web1 → web2
# ============================================================================

Vagrant.configure("2") do |config|

  # --------------------------------------------------------------------------
  # 1. ROUTER
  # --------------------------------------------------------------------------
  config.vm.define "router" do |router|
    router.vm.box = "debian/bookworm64"
    router.vm.hostname = "router.Mastertech.local"
    router.vm.network "private_network", ip: "192.168.10.1", virtualbox__intnet: "red1"
    router.vm.network "private_network", ip: "192.168.20.1", virtualbox__intnet: "red2"
    router.vm.provider "virtualbox" do |vb|
      vb.memory = "512"
      vb.cpus = 1
      vb.name = "mastertech-router"
    end
  end

  # --------------------------------------------------------------------------
  # 2. INFRA - DHCP / DNS / FTP → 192.168.10.10
  # --------------------------------------------------------------------------
  config.vm.define "infra" do |infra|
    infra.vm.box = "debian/bookworm64"
    infra.vm.hostname = "infra.Mastertech.local"
    infra.vm.network "private_network", ip: "192.168.10.10", virtualbox__intnet: "red1"
    infra.vm.provision "shell", path: "scripts/DHCPDNSFTP.sh"
    infra.vm.provider "virtualbox" do |vb|
      vb.memory = "1024"
      vb.cpus = 1
      vb.name = "mastertech-infra"
    end
  end

  # --------------------------------------------------------------------------
  # 3. NFS → 192.168.40.12  ← ANTES que web1 y web2
  # --------------------------------------------------------------------------
  config.vm.define "nfs" do |nfs|
    nfs.vm.box = "debian/bookworm64"
    nfs.vm.hostname = "nfs.Mastertech.local"
    nfs.vm.network "private_network", ip: "192.168.40.12", virtualbox__intnet: "red3"
    nfs.vm.synced_folder "./html", "/vagrant/html"
    nfs.vm.provision "shell", path: "scripts/NFS.sh"
    nfs.vm.provider "virtualbox" do |vb|
      vb.memory = "512"
      vb.cpus = 1
      vb.name = "mastertech-nfs"
    end
  end

  # --------------------------------------------------------------------------
  # 4. DB - MariaDB → 192.168.40.13  ← ANTES que web1 y web2
  # --------------------------------------------------------------------------
  config.vm.define "db" do |db|
    db.vm.box = "debian/bookworm64"
    db.vm.hostname = "db.Mastertech.local"
    db.vm.network "private_network", ip: "192.168.40.13", virtualbox__intnet: "red3"
    db.vm.provision "shell", path: "scripts/DB.sh"
    db.vm.provider "virtualbox" do |vb|
      vb.memory = "1024"
      vb.cpus = 1
      vb.name = "mastertech-db"
    end
  end

  # --------------------------------------------------------------------------
  # 5. LB - HAProxy → 192.168.20.16
  # --------------------------------------------------------------------------
  config.vm.define "lb" do |lb|
    lb.vm.box = "debian/bookworm64"
    lb.vm.hostname = "lb.Mastertech.local"
    lb.vm.network "private_network", ip: "192.168.20.16", virtualbox__intnet: "red2"
    lb.vm.network "forwarded_port", guest: 80, host: 8082
    lb.vm.provision "shell", path: "scripts/LB.sh"
    lb.vm.provider "virtualbox" do |vb|
      vb.memory = "512"
      vb.cpus = 1
      vb.name = "mastertech-lb"
    end
  end

  # --------------------------------------------------------------------------
  # 6. WEB1 - red2: 192.168.20.12 / red3: 192.168.40.10
  # --------------------------------------------------------------------------
  config.vm.define "web1" do |web1|
    web1.vm.box = "debian/bookworm64"
    web1.vm.hostname = "web1.Mastertech.local"
    web1.vm.network "private_network", ip: "192.168.20.12", virtualbox__intnet: "red2"
    web1.vm.network "private_network", ip: "192.168.40.10", virtualbox__intnet: "red3"
    web1.vm.provision "shell", path: "scripts/WEB.sh"
    web1.vm.provider "virtualbox" do |vb|
      vb.memory = "512"
      vb.cpus = 1
      vb.name = "mastertech-web1"
    end
  end

  # --------------------------------------------------------------------------
  # 7. WEB2 - red2: 192.168.20.15 / red3: 192.168.40.11
  # --------------------------------------------------------------------------
  config.vm.define "web2" do |web2|
    web2.vm.box = "debian/bookworm64"
    web2.vm.hostname = "web2.Mastertech.local"
    web2.vm.network "private_network", ip: "192.168.20.15", virtualbox__intnet: "red2"
    web2.vm.network "private_network", ip: "192.168.40.11", virtualbox__intnet: "red3"
    web2.vm.network "forwarded_port", guest: 80, host: 8081
    web2.vm.provision "shell", path: "scripts/WEB.sh"
    web2.vm.provider "virtualbox" do |vb|
      vb.memory = "512"
      vb.cpus = 1
      vb.name = "mastertech-web2"
    end
  end


  # ─────────────────────────────────────────────────────────────────────────────
  # 8. FOG Y ZABBIX
  # ─────────────────────────────────────────────────────────────────────────────

  config.vm.define "servicios" do |svc|
    svc.vm.box = "debian/bookworm64"
    svc.vm.hostname = "servicios"
    svc.vm.network "private_network", ip: "192.168.10.20", virtualbox__intnet: "red1"
    svc.vm.network "private_network", ip: "192.168.20.20", virtualbox__intnet: "red2"  # ← AÑADIR
    svc.vm.network "private_network", ip: "192.168.40.20", virtualbox__intnet: "red3"  # ← AÑADIR
    svc.vm.network "forwarded_port",  guest: 80, host: 8083
    svc.vm.provider "virtualbox" do |vb|
      vb.name   = "mastertech-servicios"
      vb.memory = 2048
      vb.cpus   = 2
    end
    svc.vm.provision "shell", path: "scripts/SERVICIOS.sh"
  end

end