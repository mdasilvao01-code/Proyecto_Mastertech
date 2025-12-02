# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.

  # -*- mode: ruby -*-
  # vi: set ft=ruby :

  # Router
  config.vm.define "router" do |router|
    router.vm.box = "debian/bookworm64"
    router.vm.hostname = "router.Mastertech.local"
    router.vm.network "private_network",
      ip: "192.168.10.1",
      virtualbox__intnet: "red1"
    router.vm.provision "shell", path: "scripts/router.sh"
  end

  # Infra (DHCP, DNS, FTP)
  config.vm.define "infra" do |infra|
    infra.vm.box = "debian/bookworm64"
    infra.vm.hostname = "infra.Mastertech.local"
    infra.vm.network "private_network", type: "dhcp"
    infra.vm.network "private_network", ip: "192.168.10.10", virtualbox__intnet: "red1"
    infra.vm.network "public_network", bridge: "VirtualBox Host-Only Ethernet Adapter"
    infra.vm.provision "shell", path: "scripts/DHCPDNSFTP.sh"
  end

  # DB (MariaDB)
  config.vm.define "db" do |db|
    db.vm.box = "debian/bookworm64"
    db.vm.hostname = "db.Mastertech.local"
    db.vm.network "private_network", ip: "192.168.10.13", virtualbox__intnet: "red1"
    db.vm.provision "shell", path: "scripts/DB.sh"
  end

  # NFS Server
  config.vm.define "nfs" do |nfs|
    nfs.vm.box = "debian/bookworm64"
    nfs.vm.hostname = "nfs.Mastertech.local"
    nfs.vm.network "private_network", ip: "192.168.10.14", virtualbox__intnet: "red1"
    nfs.vm.provision "shell", path: "scripts/NFS.sh"
  end

  # Web1
  config.vm.define "web1" do |web1|
    web1.vm.box = "debian/bookworm64"
    web1.vm.hostname = "web1.Mastertech.local"
    web1.vm.network "private_network", ip: "192.168.10.12", virtualbox__intnet: "red1"
    web1.vm.network "forwarded_port", guest: 80, host: 8080
    web1.vm.provision "shell", path: "scripts/WEB.sh"
  end

  # Web2
  config.vm.define "web2" do |web2|
    web2.vm.box = "debian/bookworm64"
    web2.vm.hostname = "web2.Mastertech.local"
    web2.vm.network "private_network", ip: "192.168.10.15", virtualbox__intnet: "red1"
    web2.vm.network "forwarded_port", guest: 80, host: 8081
    web2.vm.provision "shell", path: "scripts/WEB.sh"
  end

  # Load Balancer
  config.vm.define "lb" do |lb|
    lb.vm.box = "debian/bookworm64"
    lb.vm.hostname = "lb.Mastertech.local"
    lb.vm.network "private_network", ip: "192.168.10.16", virtualbox__intnet: "red1"
    lb.vm.network "forwarded_port", guest: 80, host: 8082
    lb.vm.provision "shell", path: "scripts/LB.sh"
  end

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine and only allow access
  # via 127.0.0.1 to disable public access
  # config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  # Disable the default share of the current code directory. Doing this
  # provides improved isolation between the vagrant box and your host
  # by making sure your Vagrantfile isn't accessible to the vagrant box.
  # If you use this you may want to enable additional shared subfolders as
  # shown above.
  # config.vm.synced_folder ".", "/vagrant", disabled: true

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Enable provisioning with a shell script. Additional provisioners such as
  # Ansible, Chef, Docker, Puppet and Salt are also available. Please see the
  # documentation for more information about their specific syntax and use.
  # config.vm.provision "shell", inline: <<-SHELL
  #   apt-get update
  #   apt-get install -y apache2
  # SHELL
end
