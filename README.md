# 🏢 Proyecto Mastertech

> Infraestructura de red empresarial completa desplegada con Vagrant + VirtualBox  
> **Mario Da Silva Ortega · 2-ASIR**

---

## 📋 Índice

- [Descripción general](#-descripción-general)
- [Arquitectura de red](#-arquitectura-de-red)
- [Máquinas virtuales](#-máquinas-virtuales)
- [Estructura del repositorio](#-estructura-del-repositorio)
- [Requisitos previos](#-requisitos-previos)
- [Despliegue rápido](#-despliegue-rápido)
- [Servicios y acceso](#-servicios-y-acceso)
- [Aplicación web (PHP)](#-aplicación-web-php)
- [Base de datos](#-base-de-datos)
- [Seguridad (iptables)](#-seguridad-iptables)
- [Administrador FTP](#-administrador-ftp)
- [Aplicación Python](#-aplicación-python)
- [Monitorización y despliegue (Zabbix + FOG)](#-monitorización-y-despliegue-zabbix--fog)
- [Comandos útiles](#-comandos-útiles)

---

## 📌 Descripción general

**Mastertech** es una infraestructura de red empresarial virtualizada que simula el entorno real de una empresa de servicios tecnológicos. Incluye:

- Red segmentada en tres subredes (empresa, DMZ, servicio)
- Servicios de red completos: DHCP, DNS, FTP
- Aplicación web PHP con base de datos MariaDB
- Balanceo de carga con HAProxy
- Almacenamiento compartido NFS
- Monitorización con Zabbix y despliegue por red con FOG
- Cortafuegos con iptables
- Herramienta de administración FTP con interfaz gráfica (Python + Bash)
- Integración con Active Directory (Windows Server)

---

## 🌐 Arquitectura de red

```
                        INTERNET
                           │
                    ┌──────┴──────┐
                    │   ROUTER    │
                    │ 10.1 / 20.1 │
                    └──────┬──────┘
           ┌───────────────┼────────────────┐
           │               │                │
    ┌──────┴──────┐  ┌──────┴──────┐  ┌──────┴──────┐
    │  RED EMPRESA│  │   RED DMZ   │  │ RED SERVICIO│
    │192.168.10.x │  │192.168.20.x │  │192.168.40.x │
    └──────┬──────┘  └──────┬──────┘  └──────┬──────┘
           │                │                │
    ┌──────┴──────┐  ┌──────┴──────┐  ┌──────┴──────┐
    │    infra    │  │     lb      │  │    web1     │
    │ DHCP/DNS/FTP│  │   HAProxy   │  │   Apache2   │
    │192.168.10.10│  │192.168.20.16│  │192.168.40.10│
    └─────────────┘  └──────┬──────┘  ├─────────────┤
    ┌─────────────┐         │         │    web2     │
    │  servicios  │  ┌──────┴──────┐  │   Apache2   │
    │Zabbix + FOG │  │ web1 / web2 │  │192.168.40.11│
    │192.168.10.20│  │20.12 / 20.15│  ├─────────────┤
    └─────────────┘  └─────────────┘  │     nfs     │
                                      │192.168.40.12│
                                      ├─────────────┤
                                      │     db      │
                                      │  MariaDB    │
                                      │192.168.40.13│
                                      └─────────────┘
```

### Subredes

| Red | Rango | Función |
|-----|-------|---------|
| red1 — Empresa | `192.168.10.0/24` | Gestión interna, DHCP, DNS, FTP, Zabbix/FOG |
| red2 — DMZ | `192.168.20.0/24` | Servicios expuestos al exterior, balanceador, webs |
| red3 — Servicio | `192.168.40.0/24` | Comunicación interna web ↔ NFS ↔ DB |

---

## 🖥️ Máquinas virtuales

| VM | Hostname | IPs | RAM | Función |
|----|----------|-----|-----|---------|
| **router** | router.Mastertech.local | 192.168.10.1 / 192.168.20.1 | 512 MB | Gateway, NAT, reenvío entre redes |
| **infra** | infra.Mastertech.local | 192.168.10.10 | 1 GB | DHCP, DNS (bind9), FTP (vsftpd) |
| **nfs** | nfs.Mastertech.local | 192.168.40.12 | 512 MB | Almacenamiento NFS compartido |
| **db** | db.Mastertech.local | 192.168.40.13 | 1 GB | Base de datos MariaDB |
| **lb** | lb.Mastertech.local | 192.168.20.16 | 512 MB | Balanceador de carga HAProxy |
| **web1** | web1.Mastertech.local | 192.168.20.12 / 192.168.40.10 | 512 MB | Servidor web Apache2 |
| **web2** | web2.Mastertech.local | 192.168.20.15 / 192.168.40.11 | 512 MB | Servidor web Apache2 |
| **servicios** | servicios | 192.168.10.20 / 192.168.20.20 / 192.168.40.20 | 2 GB | Zabbix Server + FOG Server |

> **Orden de arranque recomendado:** `router → infra → nfs → db → lb → web1 → web2 → servicios`

---

## 📁 Estructura del repositorio

```
Proyecto_Mastertech/
│
├── Vagrantfile                        # Definición completa de todas las VMs
│
├── scripts/                           # Scripts de aprovisionamiento
│   ├── router.sh                      # NAT con nftables + dnsmasq
│   ├── DHCPDNSFTP.sh                  # DHCP (isc-dhcp-server) + DNS (bind9) + FTP (vsftpd)
│   ├── NFS.sh                         # Servidor NFS exportando /var/www/html
│   ├── DB.sh                          # MariaDB + esquema BD + usuarios
│   ├── WEB.sh                         # Apache2 + PHP + montaje NFS
│   ├── LB.sh                          # HAProxy round-robin
│   ├── SERVICIOS.sh                   # Zabbix Server + FOG Server
│   ├── iptables.sh                    # Reglas de cortafuegos
│   └── ZABBIX_AGENT.sh                # Agente Zabbix para los nodos
│
├── html/                              # Aplicación web PHP (servida por NFS → Apache)
│   ├── index.php                      # Página principal / tienda
│   ├── login.php                      # Autenticación de usuarios
│   ├── registro.php                   # Registro de nuevos usuarios
│   ├── dashboard.php                  # Panel de control
│   ├── incidencias.php                # Listado de incidencias
│   ├── crear_incidencia.php           # Nueva incidencia
│   ├── ver_incidencia.php             # Detalle de incidencia + comentarios
│   ├── clientes.php                   # Gestión de clientes (admin)
│   ├── tienda.php                     # Catálogo de productos
│   ├── producto.php                   # Detalle de producto
│   ├── register_cliente.php           # Alta de cliente
│   ├── generar_informe.php            # Generación de informes
│   ├── db_config.php                  # Configuración BD + helpers de sesión
│   ├── phpinfo.php                    # Info PHP (solo desarrollo)
│   ├── logout.php                     # Cierre de sesión
│   ├── style.css                      # Hoja de estilos global
│   └── includes/
│       ├── navbar.php                 # Barra de navegación
│       └── footer.php                 # Pie de página
│
├── Aplicacion bash ftp/
│   ├── FTP.py                         # Interfaz gráfica Tkinter del administrador FTP
│   ├── Programa_ftp.sh                # Script Bash de operaciones en el servidor
│   └── Programa_ftp.zip               # Distribución empaquetada
│
├── Aplicacion_python/
│   ├── Programa.py                    # Aplicación Python de consola
│   ├── Programa_Grafico.py            # Aplicación Python con interfaz gráfica
│   ├── Instalacionpython.txt          # Instrucciones de instalación
│   └── Tarea_Grafica/                 # Recursos gráficos
│
├── BDProyecto/
│   ├── BasededatosMaster.txt          # Schema BD empresa (cliente, técnico, incidencia...)
│   ├── Empresatablas.txt              # Tablas adicionales empresa
│   ├── Webtablas.txt                  # Tablas adicionales web
│   ├── iptables.txt                   # Reglas iptables documentadas
│   └── *.dia                          # Diagramas de base de datos (Dia)
│
├── RED_Mastertech/
│   └── RED EMPRESA_MASTERTECH.pkt     # Topología en Cisco Packet Tracer
│
├── Windows/
│   ├── windows.ps1                    # Script PowerShell (Active Directory)
│   ├── Unidadorganizativa.txt         # Estructura OU del directorio
│   └── Recargadelicencia.jpeg         # Captura de pantalla
│
└── Organización Empresa/
    ├── Documentacion final/           # PDFs de documentación completa
    ├── Arquitectura/                  # Diagramas de arquitectura
    ├── BD documentacion/              # Documentación de la base de datos
    ├── Redes documentacion/           # Documentación de red
    ├── Seguridad documentacion/       # Documentación de seguridad
    └── Programas/                     # Documentación de programas
```

---

## ⚙️ Requisitos previos

- [VirtualBox](https://www.virtualbox.org/) ≥ 6.1
- [Vagrant](https://www.vagrantup.com/) ≥ 2.3
- Al menos **8 GB de RAM** disponible en el host
- Conexión a internet para descargar la box `debian/bookworm64`

---

## 🚀 Despliegue rápido

```bash
# Clonar el repositorio
git clone https://github.com/mdasilvao01-code/Proyecto_Mastertech.git
cd Proyecto_Mastertech

# Levantar toda la infraestructura
vagrant up

# O levantar máquinas en el orden correcto
vagrant up router
vagrant up infra
vagrant up nfs
vagrant up db
vagrant up lb
vagrant up web1 web2
vagrant up servicios
```

---

## 🌍 Servicios y acceso

| Servicio | URL / Conexión | Credenciales |
|----------|---------------|--------------|
| **Web (LB)** | http://localhost:8082 | — |
| **Web2 (directo)** | http://localhost:8081 | — |
| **Zabbix** | http://localhost:8083/zabbix | `Admin` / `zabbix` |
| **FOG** | http://localhost:8083/fog/management | `fog` / `password` |
| **SSH cualquier VM** | `vagrant ssh <nombre>` | Clave Vagrant |
| **FTP (red interna)** | `ftp 192.168.10.10` | `dev1:abcd` / `dev2:abcd` |

---

## 🖥️ Aplicación web (PHP)

La aplicación simula el portal de gestión de servicios de MasterTech.AI. Está construida en PHP y se sirve desde los nodos web1 y web2 a través del almacenamiento compartido NFS.

### Funcionalidades

| Módulo | Archivo | Roles permitidos |
|--------|---------|-----------------|
| Inicio / Tienda | `index.php`, `tienda.php` | Todos |
| Login / Registro | `login.php`, `registro.php` | Todos |
| Dashboard | `dashboard.php` | Autenticado |
| Incidencias | `incidencias.php`, `crear_incidencia.php`, `ver_incidencia.php` | Autenticado |
| Clientes | `clientes.php`, `register_cliente.php` | Admin / Técnico |
| Productos | `producto.php` | Todos |
| Informes | `generar_informe.php` | Admin |

### Roles de usuario

- **admin** — Acceso total al sistema
- **tecnico** — Gestión de incidencias y clientes
- **cliente** — Consulta y creación de incidencias propias

### Configuración de base de datos (`db_config.php`)

```php
$host     = "192.168.40.13";   // Servidor DB en red servicio
$usuario  = "webuser";
$password = "WebPass@2024";
$database = "mastertech";
```

### Flujo de la aplicación

```
Usuario → LB (192.168.20.16:80)
         ↓ round-robin
    web1 (192.168.20.12) ──→ NFS (192.168.40.12) ──→ /var/www/html
    web2 (192.168.20.15) ──→ NFS (192.168.40.12) ──→ /var/www/html
         ↓ consultas SQL
         DB (192.168.40.13:3306)
```

---

## 🗄️ Base de datos

MariaDB en `192.168.40.13`, base de datos `mastertech`.

### Tablas principales

```sql
usuarios        -- Cuentas de acceso (admin, tecnico, cliente)
incidencias     -- Tickets de soporte con prioridad y estado
comentarios     -- Comentarios en incidencias
cliente         -- Datos de clientes de la empresa
productos       -- Catálogo de productos / servicios
app_logs        -- Registro de acciones de la aplicación
```

### Tablas del modelo empresa

```sql
tecnico             -- Personal técnico con especialidad
servicio            -- Tipos de servicio con precio
equipo              -- Equipos registrados (marca, modelo, serie)
incidencia          -- Incidencias vinculadas a cliente y equipo
reparacion          -- Reparaciones con técnico asignado
reparacion_servicio -- Servicios aplicados en cada reparación
```

### Acceso a la base de datos

```bash
# Desde web1 o web2 (red servicio 192.168.40.x)
mysql -h 192.168.40.13 -u webuser -pWebPass@2024 mastertech

# Desde la propia VM db
vagrant ssh db
sudo mysql mastertech
```

### Usuario por defecto

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@mastertech.local | admin1234 | admin |

---

## 🔒 Seguridad (iptables)

Política aplicada en todas las máquinas: **todo bloqueado por defecto**, solo se permite el tráfico necesario.

```bash
# Política por defecto
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT

# Loopback
iptables -A INPUT -i lo -j ACCEPT

# SSH solo desde el router
iptables -A INPUT -p tcp --dport 22 -s 192.168.10.1 -j ACCEPT

# MariaDB solo desde web1
iptables -A INPUT -p tcp --dport 3306 -s 192.168.10.12 -j ACCEPT

# FTP solo desde red interna
iptables -A INPUT -p tcp --dport 21 -s 192.168.10.0/24 -j ACCEPT

# Registro de paquetes bloqueados
iptables -A INPUT -j LOG --log-prefix "IPTABLES DROP: "

# Persistencia
iptables-save > /etc/iptables.rules
```

### Tabla de reglas por servicio

| Puerto | Proto | Origen | Destino | Servicio |
|--------|-------|--------|---------|----------|
| lo | — | localhost | Todas | Loopback |
| 22 | TCP | 192.168.10.1 / 192.168.0.0/16 | Todas | SSH administración |
| 3306 | TCP | 192.168.40.10 / 40.11 | db | MariaDB |
| 21 | TCP | 192.168.10.0/24 | infra | FTP |
| 80 | TCP | 192.168.20.16 | web1, web2 | HTTP desde LB |
| 2049 | TCP | 192.168.40.10 / 40.11 | nfs | NFS |
| 53 | UDP | 192.168.10.0/24 | infra | DNS |
| 67-68 | UDP | 192.168.10.0/24 | infra | DHCP |

---

## 📂 Administrador FTP

Herramienta gráfica de administración remota del servidor FTP. Formada por dos ficheros:

| Fichero | Ubicación | Función |
|---------|-----------|---------|
| `FTP.py` | Equipo del administrador | Interfaz gráfica Tkinter |
| `Programa_ftp.sh` | Servidor infra (`/home/infra/`) | Operaciones del sistema |

### Requisitos

```bash
# En el equipo del administrador
# Python 3 + cliente SSH con clave pública configurada
ssh-keygen -t ed25519
ssh-copy-id infra@192.168.10.10

# En el servidor infra
chmod +x /home/infra/Programa_ftp.sh
```

### Uso

```bash
python3 FTP.py
```

### Acciones disponibles

| Acción | Usuario | Ruta | Permisos |
|--------|---------|------|----------|
| Crear Usuario | ✅ | — | — |
| Crear Usuario + Carpeta | ✅ | ✅ | — |
| Borrar Usuario | ✅ | — | — |
| Crear Carpeta | — | ✅ | — |
| Borrar Carpeta | — | ✅ | — |
| Cambiar Permisos | — | ✅ | ✅ |
| Cambiar Propietario | ✅ | ✅ | — |
| Listar | — | ✅ (absoluta) | — |

Todas las operaciones quedan registradas en `/home/infra/ftp_admin.log`.

---

## 🐍 Aplicación Python

Dos versiones de una aplicación de gestión incluidas en `Aplicacion_python/`:

- **`Programa.py`** — Versión de consola (terminal)
- **`Programa_Grafico.py`** — Versión con interfaz gráfica (Tkinter)

```bash
# Instalar dependencias (ver Instalacionpython.txt)
pip3 install -r requirements.txt

# Ejecutar versión gráfica
python3 Programa_Grafico.py
```

---

## 📊 Monitorización y despliegue (Zabbix + FOG)

La VM `servicios` (`192.168.10.20`) centraliza los servicios de gestión de la infraestructura.

### Zabbix

Sistema de monitorización que supervisa todos los nodos de la red.

```
Acceso: http://localhost:8083/zabbix
Usuario: Admin
Contraseña: zabbix
```

Hosts monitorizados (añadir con el script incluido):

```bash
vagrant ssh servicios
sudo bash /usr/local/bin/zabbix_add_hosts.sh
```

| Host | IP monitizada |
|------|--------------|
| router | 192.168.10.1 |
| infra | 192.168.10.10 |
| lb | 192.168.20.16 |
| web1 | 192.168.20.12 |
| web2 | 192.168.20.15 |
| nfs | 192.168.40.12 |
| db | 192.168.40.13 |

### FOG Server

Sistema de despliegue por red (clonado de imágenes PXE).

```
Acceso: http://localhost:8083/fog/management
Usuario: fog
Contraseña: password
```

---

## 🛠️ Comandos útiles

```bash
# Gestión básica
vagrant up                    # Levantar todo
vagrant up <nombre>           # Levantar una VM
vagrant halt                  # Apagar todo
vagrant halt <nombre>         # Apagar una VM
vagrant reload <nombre>       # Reiniciar una VM
vagrant destroy -f            # Eliminar todas las VMs

# Acceso SSH
vagrant ssh router
vagrant ssh infra
vagrant ssh db
vagrant ssh web1
vagrant ssh lb

# Ver estado de todas las VMs
vagrant status

# Re-ejecutar aprovisionamiento
vagrant provision <nombre>

# Verificar servicios desde el host
curl http://localhost:8082          # Web a través del balanceador
curl http://localhost:8081          # Web2 directo
curl http://localhost:8083/zabbix   # Zabbix
```

---

## 📚 Tecnologías utilizadas

| Tecnología | Versión | Uso |
|-----------|---------|-----|
| Debian Bookworm | 12 | SO base de todas las VMs |
| Vagrant | ≥ 2.3 | Gestión del entorno virtualizado |
| VirtualBox | ≥ 6.1 | Hipervisor |
| Apache2 | 2.4 | Servidor web |
| PHP | 8.2 | Lenguaje de la aplicación web |
| MariaDB | 10.x | Base de datos relacional |
| HAProxy | 2.x | Balanceador de carga |
| NFS | — | Almacenamiento compartido |
| bind9 | — | Servidor DNS |
| isc-dhcp-server | — | Servidor DHCP |
| vsftpd | — | Servidor FTP |
| nftables | — | NAT en el router |
| iptables | — | Cortafuegos en los nodos |
| Zabbix | 6.4 | Monitorización |
| FOG | latest | Despliegue por red |
| Python 3 | — | Aplicaciones de gestión |
| Tkinter | — | Interfaces gráficas Python |

---

> **Mario Da Silva Ortega** · 2-ASIR · Proyecto MASTERTECH
