#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

echo "🔧 Configurando MariaDB en db (192.168.40.13)..."

echo "nameserver 8.8.8.8" > /etc/resolv.conf

apt-get update -y
apt-get install -y mariadb-server

# Aceptar conexiones desde la RED SERVICIO
sed -i 's/^bind-address\s*=.*/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf

systemctl enable mariadb
systemctl start mariadb

mysql -u root <<'SQLEOF'

CREATE DATABASE IF NOT EXISTS mastertech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mastertech;

CREATE TABLE IF NOT EXISTS usuarios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  rol         ENUM('admin','tecnico','cliente') DEFAULT 'cliente',
  empresa     VARCHAR(100),
  telefono    VARCHAR(20),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS incidencias (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  titulo       VARCHAR(200) NOT NULL,
  descripcion  TEXT,
  prioridad    ENUM('baja','media','alta','critica') DEFAULT 'media',
  estado       ENUM('abierta','en_proceso','resuelta','cerrada') DEFAULT 'abierta',
  categoria    VARCHAR(100),
  cliente_id   INT,
  tecnico_id   INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS comentarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  incidencia_id  INT NOT NULL,
  usuario_id     INT NOT NULL,
  comentario     TEXT NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id)    REFERENCES usuarios(id)    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(100),
  email      VARCHAR(100),
  telefono   VARCHAR(20),
  empresa    VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS productos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(150) NOT NULL,
  descripcion TEXT,
  precio      DECIMAL(10,2),
  stock       INT DEFAULT 0,
  categoria   VARCHAR(100),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS app_logs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  ip_origen  VARCHAR(45),
  usuario    VARCHAR(100),
  accion     VARCHAR(200),
  detalles   TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Acceso desde web1 (192.168.40.10) y web2 (192.168.40.11) - RED SERVICIO
CREATE USER IF NOT EXISTS 'webuser'@'192.168.40.10' IDENTIFIED BY 'WebPass@2024';
GRANT ALL PRIVILEGES ON mastertech.* TO 'webuser'@'192.168.40.10';

CREATE USER IF NOT EXISTS 'webuser'@'192.168.40.11' IDENTIFIED BY 'WebPass@2024';
GRANT ALL PRIVILEGES ON mastertech.* TO 'webuser'@'192.168.40.11';

INSERT IGNORE INTO usuarios (nombre, email, password, rol)
VALUES ('Admin', 'admin@mastertech.local', SHA2('admin1234', 256), 'admin');

FLUSH PRIVILEGES;
SQLEOF

systemctl restart mariadb

echo "✅ MariaDB listo en 192.168.40.13. Acceso desde web1 (40.10) y web2 (40.11)"