# 🏢 Proyecto MASTERTECH

> Infraestructura de red empresarial completa — Vagrant · VirtualBox · PHP · MariaDB · HAProxy · Zabbix · FOG  
> **Mario Da Silva Ortega · 2-ASIR · IES Albarregas · 2025-2026**

[![Debian](https://img.shields.io/badge/OS-Debian%20Bookworm-red?logo=debian)](https://www.debian.org/)
[![Vagrant](https://img.shields.io/badge/Vagrant-≥2.3-blue?logo=vagrant)](https://www.vagrantup.com/)
[![VirtualBox](https://img.shields.io/badge/VirtualBox-≥6.1-blue?logo=virtualbox)](https://www.virtualbox.org/)
[![PHP](https://img.shields.io/badge/PHP-8.2-purple?logo=php)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.x-blue?logo=mariadb)](https://mariadb.org/)
[![Zabbix](https://img.shields.io/badge/Zabbix-6.4-red?logo=zabbix)](https://www.zabbix.com/)

---

## 📋 Índice

- [Descripción general](#-descripción-general)
- [Arquitectura de red](#-arquitectura-de-red)
- [Máquinas virtuales](#-máquinas-virtuales)
- [Estructura del repositorio](#-estructura-del-repositorio)
- [Requisitos previos](#-requisitos-previos)
- [Despliegue rápido](#-despliegue-rápido)
- [Servicios y acceso](#-servicios-y-acceso)
- [Aplicación web PHP](#-aplicación-web-php)
- [Base de datos MariaDB](#-base-de-datos-mariadb)
- [Seguridad — iptables](#-seguridad--iptables)
- [Administrador FTP](#-administrador-ftp)
- [Sistema de incidencias Python](#-sistema-de-incidencias-python)
- [Active Directory — Windows Server](#-active-directory--windows-server)
- [Monitorización — Zabbix](#-monitorización--zabbix)
- [Despliegue por red — FOG](#-despliegue-por-red--fog)
- [Comandos útiles](#-comandos-útiles)
- [Tecnologías utilizadas](#-tecnologías-utilizadas)

---

## 📌 Descripción general

**Mastertech** es una infraestructura empresarial virtualizada completa que simula el entorno real de una empresa de servicios tecnológicos (MasterTech.AI). El proyecto integra:

| Área | Tecnología | Descripción |
|------|-----------|-------------|
| 🌐 Red | Vagrant + VirtualBox | 3 subredes segmentadas (empresa, DMZ, servicio) |
| 🖥️ Web | Apache2 + PHP 8.2 | Portal de gestión de incidencias con balanceo de carga |
| 🗄️ Base de datos | MariaDB 10.x | Esquema completo con roles y auditoría |
| ⚖️ Balanceo | HAProxy | Distribución round-robin entre web1 y web2 |
| 📁 Almacenamiento | NFS | Contenido web compartido entre servidores |
| 🔒 Seguridad | iptables | Política de mínimo privilegio en todos los nodos |
| 📡 DNS / DHCP / FTP | bind9 + isc-dhcp + vsftpd | Servicios de red internos centralizados |
| 📊 Monitorización | Zabbix 6.4 | Supervisión en tiempo real de todos los nodos |
| 💾 Despliegue | FOG Project | Clonado e imagen de equipos vía PXE |
| 🐍 Herramientas | Python 3 + Tkinter | Administrador FTP gráfico + Sistema de incidencias |
| 🏢 Directorio | Active Directory | Dominio MasterTech.lan con OUs, usuarios y GPOs |

---

## 🌐 Arquitectura de red

```
                              INTERNET
                                 │
                    ┌────────────┴────────────┐
                    │         ROUTER          │
                    │  192.168.10.1 (red1)    │
                    │  192.168.20.1 (red2)    │
                    │     NAT → WAN           │
                    └────────┬────────────────┘
          ┌──────────────────┼──────────────────────┐
          │                  │                       │
  ┌───────┴────────┐  ┌──────┴──────────┐  ┌────────┴────────┐
  │  RED EMPRESA   │  │    RED DMZ      │  │  RED SERVICIO   │
  │ 192.168.10.0/24│  │192.168.20.0/24  │  │192.168.40.0/24  │
  └───────┬────────┘  └──────┬──────────┘  └────────┬────────┘
          │                  │                       │
  ┌───────┴────────┐  ┌──────┴──────────┐  ┌────────┴────────┐
  │     infra      │  │       lb        │  │  web1 / web2    │
  │ DHCP·DNS·FTP   │  │    HAProxy      │  │ Apache2 + PHP   │
  │ 192.168.10.10  │  │ 192.168.20.16   │  │  40.10 / 40.11  │
  ├────────────────┤  ├─────────────────┤  ├─────────────────┤
  │   servicios    │  │  web1 / web2    │  │      nfs        │
  │ Zabbix + FOG   │  │  20.12 / 20.15  │  │ 192.168.40.12   │
  │ 192.168.10.20  │  └─────────────────┘  ├─────────────────┤
  └────────────────┘                        │       db        │
                                            │ 192.168.40.13   │
                                            └─────────────────┘
```

### Subredes

| Red | Rango | Propósito |
|-----|-------|-----------|
| **red1** — Empresa | `192.168.10.0/24` | Gestión interna: DHCP, DNS, FTP, Zabbix, FOG |
| **red2** — DMZ | `192.168.20.0/24` | Servicios públicos: balanceador y frontends web |
| **red3** — Servicio | `192.168.40.0/24` | Comunicación interna: web ↔ NFS ↔ DB |

---

## 🖥️ Máquinas virtuales

| VM | Hostname | IP(s) | RAM | Script | Función |
|----|----------|-------|-----|--------|---------|
| **router** | router.Mastertech.local | `10.1` · `20.1` | 512 MB | `router.sh` | Gateway, NAT entre redes |
| **infra** | infra.Mastertech.local | `10.10` | 1 GB | `DHCPDNSFTP.sh` | DHCP · DNS (bind9) · FTP (vsftpd) |
| **nfs** | nfs.Mastertech.local | `40.12` | 512 MB | `NFS.sh` | Almacenamiento NFS `/var/www/html` |
| **db** | db.Mastertech.local | `40.13` | 1 GB | `DB.sh` | MariaDB — base de datos principal |
| **lb** | lb.Mastertech.local | `20.16` | 512 MB | `LB.sh` | HAProxy round-robin → puerto 8082 |
| **web1** | web1.Mastertech.local | `20.12` · `40.10` | 512 MB | `WEB.sh` | Apache2 + PHP + NFS |
| **web2** | web2.Mastertech.local | `20.15` · `40.11` | 512 MB | `WEB.sh` | Apache2 + PHP → puerto 8081 |
| **servicios** | servicios | `10.20` · `20.20` · `40.20` | 2 GB | `SERVICIOS.sh` | Zabbix 6.4 + FOG → puerto 8083 |

> **⚠️ Orden de arranque:** `router → infra → nfs → db → lb → web1 → web2 → servicios`

---

## 📁 Estructura del repositorio

```
Proyecto_Mastertech/
│
├── Vagrantfile                          # Definición completa de las 8 VMs
│
├── scripts/                             # Scripts de aprovisionamiento Bash
│   ├── router.sh                        # nftables NAT + dnsmasq
│   ├── DHCPDNSFTP.sh                    # isc-dhcp-server + bind9 (3 zonas) + vsftpd
│   ├── NFS.sh                           # nfs-kernel-server + exportación /var/www/html
│   ├── DB.sh                            # MariaDB + esquema completo + usuarios webuser
│   ├── WEB.sh                           # Apache2 + PHP + montaje NFS desde red3
│   ├── LB.sh                            # HAProxy con health check round-robin
│   ├── SERVICIOS.sh                     # Zabbix Server 6.4 + FOG Project + API script
│   ├── iptables.sh                      # Política DROP + reglas por servicio
│   └── ZABBIX_AGENT.sh                  # Agente Zabbix parametrizable (hostname + IP)
│
├── html/                                # Aplicación web PHP (desplegada vía NFS)
│   ├── index.php                        # Página principal / tienda
│   ├── login.php                        # Autenticación de usuarios
│   ├── registro.php                     # Registro de nuevos usuarios
│   ├── dashboard.php                    # Panel de control
│   ├── incidencias.php                  # Listado de incidencias
│   ├── crear_incidencia.php             # Formulario nueva incidencia
│   ├── ver_incidencia.php               # Detalle de incidencia + comentarios
│   ├── clientes.php                     # Gestión de clientes (admin/técnico)
│   ├── tienda.php                       # Catálogo de productos
│   ├── producto.php                     # Ficha de producto
│   ├── register_cliente.php             # Alta de cliente
│   ├── generar_informe.php              # Informes exportables (admin)
│   ├── db_config.php                    # Conexión BD + funciones de sesión y auditoría
│   ├── logout.php                       # Cierre de sesión
│   ├── phpinfo.php                      # Info PHP (solo desarrollo)
│   ├── style.css                        # Hoja de estilos global
│   └── includes/
│       ├── navbar.php                   # Barra de navegación por roles
│       └── footer.php                   # Pie de página
│
├── Aplicacion bash ftp/
│   ├── FTP.py                           # GUI Tkinter — administrador FTP remoto
│   ├── Programa_ftp.sh                  # Script Bash ejecutado en el servidor infra
│   └── Programa_ftp.zip                 # Distribución empaquetada
│
├── Aplicacion_python/
│   ├── Programa.py                      # Sistema de incidencias — versión consola
│   ├── Programa_Grafico.py              # Sistema de incidencias — versión GUI Tkinter
│   ├── Instalacionpython.txt            # Instrucciones de instalación
│   └── Tarea_Grafica/
│       ├── crear_incidencia.sh          # Script bash — crear incidencia
│       ├── modificar_incidencia.sh      # Script bash — modificar incidencia
│       ├── ver_incidencias.sh           # Script bash — listar incidencias
│       └── Programa_Graficolinux.py     # Versión adaptada para Linux
│
├── BDProyecto/
│   ├── BasededatosMaster.txt            # Schema BD empresa (cliente, técnico, reparación...)
│   ├── Webtablas.txt                    # Schema BD web (empresa_cliente, tickets, facturas...)
│   ├── Empresatablas.txt                # Tablas adicionales empresa
│   ├── iptables.txt                     # Reglas iptables documentadas
│   ├── Mastertech(empresa).dia          # Diagrama E-R empresa (Dia)
│   ├── Mastertech(web).dia              # Diagrama E-R web (Dia)
│   └── Modeloproyecto.dia               # Modelo general del proyecto (Dia)
│
├── RED_Mastertech/
│   └── RED EMPRESA_MASTERTECH.pkt       # Topología Cisco Packet Tracer
│
├── Windows/
│   ├── windows.ps1                      # PowerShell: instala IIS + FTP Server
│   └── Unidadorganizativa.txt           # Árbol de OUs del dominio MasterTech.lan
│
└── Organización Empresa/
    ├── Documentacion final/
    │   ├── MASTERTECH-Sistema-de-Gestion-de-Incidencias.pdf
    │   └── Proyecto_Final_MasterTech.docx
    ├── Arquitectura/                    # Diagramas de arquitectura de red
    ├── BD documentacion/                # Documentación base de datos
    ├── Redes documentacion/             # Documentación infraestructura de red
    ├── Seguridad documentacion/         # Documentación iptables y seguridad
    └── Programas/                       # Documentación de aplicaciones
```

---

## ⚙️ Requisitos previos

| Requisito | Versión mínima | Notas |
|-----------|---------------|-------|
| [VirtualBox](https://www.virtualbox.org/) | 6.1 | Hipervisor base |
| [Vagrant](https://www.vagrantup.com/) | 2.3 | Gestión de VMs |
| RAM disponible | 8 GB | Mínimo para levantar todas las VMs |
| Disco libre | 20 GB | Para las imágenes de las VMs |
| Conexión internet | — | Para descargar `debian/bookworm64` |

---

## 🚀 Despliegue rápido

```bash
# 1. Clonar el repositorio
git clone https://github.com/mdasilvao01-code/Proyecto_Mastertech.git
cd Proyecto_Mastertech

# 2. Levantar toda la infraestructura de una vez
vagrant up

# ── O en orden manual (recomendado la primera vez) ──────────────────────────
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

| Servicio | URL desde el host | Credenciales |
|----------|------------------|--------------|
| **Portal web** (balanceador) | http://localhost:8082 | `admin@mastertech.local` / `admin1234` |
| **Web2** (directo) | http://localhost:8081 | — |
| **Zabbix** | http://localhost:8083/zabbix | `Admin` / `zabbix` |
| **FOG** | http://localhost:8083/fog/management | `fog` / `password` |
| **SSH** a cualquier VM | `vagrant ssh <nombre>` | Clave Vagrant |
| **FTP** (red interna) | `ftp 192.168.10.10` | `dev1:abcd` / `dev2:abcd` |
| **MariaDB** (red servicio) | `192.168.40.13:3306` | `webuser` / `WebPass@2024` |

---

## 🖥️ Aplicación web PHP

Portal de gestión de servicios de MasterTech.AI. Desplegada en Apache2, contenido compartido vía NFS y persistencia en MariaDB.

### Flujo de datos

```
Usuario → LB:8082 (192.168.20.16)
             │ round-robin
    ┌────────┴────────┐
    ▼                 ▼
web1 (20.12/40.10)   web2 (20.15/40.11)
    │                 │
    └────────┬────────┘
             ▼
       NFS (40.12) → /var/www/html
             │
             ▼
       DB (40.13:3306) → mastertech
```

### Módulos y roles

| Módulo | Archivos | admin | técnico | cliente |
|--------|----------|:-----:|:-------:|:-------:|
| Inicio / Tienda | `index.php`, `tienda.php` | ✅ | ✅ | ✅ |
| Login / Registro | `login.php`, `registro.php` | ✅ | ✅ | ✅ |
| Dashboard | `dashboard.php` | ✅ | ✅ | ✅ |
| Gestión incidencias | `incidencias.php`, `crear_incidencia.php`, `ver_incidencia.php` | ✅ | ✅ | ✅ |
| Gestión clientes | `clientes.php`, `register_cliente.php` | ✅ | ✅ | ❌ |
| Catálogo / Productos | `tienda.php`, `producto.php` | ✅ | ✅ | ✅ |
| Informes | `generar_informe.php` | ✅ | ❌ | ❌ |

### Conexión a la base de datos (`db_config.php`)

```php
$host     = "192.168.40.13";   // DB en red servicio
$usuario  = "webuser";
$password = "WebPass@2024";
$database = "mastertech";
```

---

## 🗄️ Base de datos MariaDB

Servidor en `192.168.40.13`, base de datos `mastertech`, charset `utf8mb4`.

### Esquema — BD web (aplicación PHP)

```sql
usuarios         -- Cuentas con rol: admin | tecnico | cliente
incidencias      -- Tickets con prioridad (baja/media/alta/critica) y estado
comentarios      -- Seguimiento de incidencias (FK → incidencias, usuarios)
cliente          -- Datos de clientes externos
productos        -- Catálogo con precio, stock y categoría
app_logs         -- Auditoría: IP, usuario, acción, timestamp
```

### Esquema — BD empresa (modelo completo)

```sql
EMPRESA_CLIENTE      -- Empresas cliente con sector y contacto
CONTACTO_EMPRESA     -- Contactos por empresa (FK → EMPRESA_CLIENTE)
SERVICIOS            -- Catálogo de servicios con precio base
CONTRATOS            -- Contratos empresa↔servicio con fechas y estado
EQUIPOS              -- Inventario de equipos por empresa
TICKETS              -- Incidencias vinculadas a contratos
REPARACIONES         -- Reparaciones con diagnóstico y solución
FACTURAS             -- Facturación por contrato con total
tecnico              -- Personal técnico con especialidad
reparacion           -- Reparaciones asignadas a técnico
reparacion_servicio  -- Servicios aplicados en cada reparación
```

### Acceso

```bash
# Desde web1 o web2 (red servicio)
mysql -h 192.168.40.13 -u webuser -pWebPass@2024 mastertech

# Desde la VM db
vagrant ssh db && sudo mysql mastertech
```

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@mastertech.local | admin1234 | admin |

---

## 🔒 Seguridad — iptables

Política en todos los nodos: **DROP por defecto**, solo se permite el tráfico estrictamente necesario.

```bash
# Política por defecto
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT

# Loopback
iptables -A INPUT -i lo -j ACCEPT

# SSH — solo desde el router y la red interna
iptables -A INPUT -p tcp --dport 22 -s 192.168.10.1   -j ACCEPT
iptables -A INPUT -p tcp --dport 22 -s 192.168.0.0/16 -j ACCEPT

# MariaDB — solo desde web1 y web2 (red servicio)
iptables -A INPUT -p tcp --dport 3306 -s 192.168.40.10 -j ACCEPT
iptables -A INPUT -p tcp --dport 3306 -s 192.168.40.11 -j ACCEPT

# FTP — solo desde la red interna
iptables -A INPUT -p tcp --dport 21 -s 192.168.10.0/24 -j ACCEPT

# LOG de paquetes bloqueados
iptables -A INPUT -j LOG --log-prefix "IPTABLES DROP: "

# Persistencia tras reinicio
iptables-save > /etc/iptables.rules
```

### Tabla de reglas

| Puerto | Proto | Origen permitido | Destino | Servicio |
|--------|-------|-----------------|---------|----------|
| `lo` | — | localhost | Todas | Loopback |
| `22` | TCP | `10.1` / `0.0/16` | Todas | SSH administración |
| `3306` | TCP | `40.10` · `40.11` | db | MariaDB desde webs |
| `21` | TCP | `10.0/24` | infra | FTP interno |
| `80` | TCP | `20.16` | web1, web2 | HTTP desde balanceador |
| `2049` | TCP | `40.10` · `40.11` | nfs | NFS desde webs |
| `53` | UDP | `10.0/24` | infra | DNS interno |
| `67-68` | UDP | `10.0/24` | infra | DHCP interno |

### Prevención de DoS

```bash
iptables -A INPUT -p tcp --dport 22 \
  -m connlimit --connlimit-above 10 -j REJECT
```

---

## 📂 Administrador FTP

Herramienta gráfica de administración remota del servidor FTP. Comunicación via SSH sin contraseña.

```
[ Administrador ]                      [ infra — 192.168.10.10 ]
    FTP.py  ──── SSH (BatchMode) ────►  Programa_ftp.sh
       │         accion + params              │
       │◄──────── stdout ────────────────────  │
    Salida en pantalla                 /home/infra/ftp_admin.log
```

### Configuración SSH previa

```bash
# En el equipo del administrador
ssh-keygen -t ed25519 -C "ftp-admin"
ssh-copy-id infra@192.168.10.10

# En el servidor infra
chmod +x /home/infra/Programa_ftp.sh

# Ejecutar
python3 FTP.py
```

### Acciones disponibles

| Acción | Campos requeridos | Notas |
|--------|------------------|-------|
| Crear Usuario | Usuario | Crea home en `/home/usuario` |
| Crear Usuario + Carpeta | Usuario · Ruta | Relativa → se crea en `/home/usuario/` |
| Borrar Usuario | Usuario | ⚠️ Irreversible — elimina home completo |
| Crear Carpeta | Ruta | Soporta `mkdir -p` (rutas anidadas) |
| Borrar Carpeta | Ruta | ⚠️ Rutas críticas del sistema protegidas |
| Cambiar Permisos | Ruta · Permisos | Formato octal: `755`, `644`, `700`... |
| Cambiar Propietario | Usuario · Ruta | Aplica `chown -R` recursivo |
| Listar | Ruta absoluta | Muestra `ls -lah` del directorio |

> Todas las operaciones se registran en `/home/infra/ftp_admin.log` con timestamp.

---

## 🐍 Sistema de incidencias Python

Aplicación independiente de gestión de incidencias con dos versiones.

```
Aplicacion_python/
├── Programa.py              ← Versión terminal (consola)
├── Programa_Grafico.py      ← Versión GUI Tkinter (800×600)
└── Tarea_Grafica/
    ├── crear_incidencia.sh
    ├── modificar_incidencia.sh
    └── ver_incidencias.sh
```

**Funcionalidades:**
- Login por nombre de usuario + rol (`admin` / `tecnico` / `cliente`)
- Crear, ver, modificar y cerrar incidencias almacenadas en ficheros locales
- Interfaz gráfica con formularios de edición por rol
- Scripts Bash complementarios para operaciones desde terminal

```bash
python3 Aplicacion_python/Programa_Grafico.py   # Versión gráfica
python3 Aplicacion_python/Programa.py           # Versión consola
```

---

## 🏢 Active Directory — Windows Server

Dominio **MasterTech.lan** con estructura de OUs que refleja la organización empresarial real.

### Árbol de OUs

```
MasterTech.lan
├── España
│   ├── Madrid
│   │   ├── Usuarios · Equipos · Administración
│   │   └── Departamentos
│   │       ├── Sistemas
│   │       ├── Soporte
│   │       └── Comercial
│   ├── Barcelona · Sevilla · Valencia · Bilbao
├── Servicios
│   └── Web · FTP · BaseDeDatos · DNS · Impresoras
└── Seguridad
    └── Logs · Backups
```

### Grupos de seguridad

| Grupo | Ámbito |
|-------|--------|
| `G_Madrid_Sistemas` | Administración de sistemas en Madrid |
| `G_Madrid_Soporte` | Soporte técnico presencial |
| `G_Madrid_Comercial` | Acceso comercial y ventas |
| `G_Servicios_FTP` | Acceso y gestión del servidor FTP |
| `G_Servicios_BD` | Acceso a bases de datos |
| `G_Seguridad_Logs` | Lectura de logs de auditoría |
| `G_Seguridad_Backups` | Gestión de copias de seguridad |

### Usuarios de ejemplo

| Usuario | Nombre | Grupos | OU |
|---------|--------|--------|-----|
| `jlopez` | Juan López | G_Madrid_Sistemas | Madrid\Departamentos\Sistemas |
| `mgarcia` | Marta García | G_Madrid_Soporte | Madrid\Departamentos\Soporte |
| `rfernandez` | Raúl Fernández | G_Madrid_Comercial | Madrid\Departamentos\Comercial |
| `asanchez` | Antonio Sánchez | G_Servicios_Web · G_Servicios_DNS | Servicios\Web |
| `pnavarro` | Pilar Navarro | G_Seguridad_Backups | Seguridad\Backups |

### GPOs configuradas

| GPO | Aplicada a | Objetivo |
|-----|-----------|----------|
| `GPO_Usuarios_Madrid` | Madrid\Usuarios | Escritorio y configuración de red |
| `GPO_Sistemas` | Madrid\Departamentos\Sistemas | Permisos de administración y scripts |
| `GPO_Soporte` | Madrid\Departamentos\Soporte | Herramientas de soporte remoto |
| `GPO_Comercial` | Madrid\Departamentos\Comercial | Restricciones de navegación |
| `GPO_Seguridad` | Seguridad | Auditoría, logs y backups |

---

## 📊 Monitorización — Zabbix

Zabbix 6.4 instalado en `servicios` (192.168.10.20). Supervisa todos los nodos en tiempo real.

### Acceso

```
URL:      http://localhost:8083/zabbix
Usuario:  Admin
Password: zabbix
```

### Hosts monitorizados

| Host | IP | Red | Función |
|------|----|-----|---------|
| router | 192.168.10.1 | red1 | Gateway y NAT |
| infra | 192.168.10.10 | red1 | DHCP · DNS · FTP |
| servicios | 192.168.10.20 | red1 | Zabbix + FOG |
| lb | 192.168.20.16 | red2 | Balanceador HAProxy |
| web1 | 192.168.20.12 | red2 | Servidor web Apache |
| web2 | 192.168.20.15 | red2 | Servidor web Apache |
| nfs | 192.168.40.12 | red3 | Almacenamiento NFS |
| db | 192.168.40.13 | red3 | Base de datos MariaDB |

### Alta automática de hosts

```bash
vagrant ssh servicios
sudo bash /usr/local/bin/zabbix_add_hosts.sh
```

### Instalar agente en cualquier nodo

```bash
# Uso: bash ZABBIX_AGENT.sh <hostname> <ip>
bash scripts/ZABBIX_AGENT.sh web1 192.168.20.12
bash scripts/ZABBIX_AGENT.sh db   192.168.40.13
```

---

## 💾 Despliegue por red — FOG

FOG Project en `servicios`. Captura y despliega imágenes de SO vía PXE sin intervención manual.

### Acceso

```
URL:      http://localhost:8083/fog/management
Usuario:  fog
Password: password
```

### Operaciones

| Operación | Ruta en el panel | Pasos |
|-----------|-----------------|-------|
| Registrar equipo | Hosts → Add New Host | Nombre + MAC + imagen asignada |
| Capturar imagen | Hosts → Basic Tasks → Capture | Schedule → arrancar por PXE |
| Desplegar imagen | Hosts → Basic Tasks → Deploy | Schedule → arrancar por PXE |
| Despliegue masivo | Groups → seleccionar grupo | Tarea de grupo (multicast) |

### Configuración PXE en DHCP (infra)

```bash
# Añadir en /etc/dhcp/dhcpd.conf
next-server 192.168.10.20;
filename "undionly.kkpxe";    # BIOS
# filename "snponly.efi";     # UEFI
```

### Mejora vs despliegue manual

| Métrica | Sin FOG | Con FOG |
|---------|---------|---------|
| Reinstalación | 2-4 horas | 10-15 minutos |
| Despliegue múltiple | 1 día o más | Minutos (multicast) |
| Recuperación ante fallo | Reinstalación completa | Restauración desde imagen |

---

## 🛠️ Comandos útiles

```bash
# ── Gestión de VMs ────────────────────────────────────────────────────────────
vagrant up                      # Levantar todas las VMs
vagrant up <nombre>             # Levantar una VM específica
vagrant halt                    # Apagar todas las VMs
vagrant reload <nombre>         # Reiniciar una VM
vagrant destroy -f              # Eliminar todas las VMs
vagrant status                  # Ver estado de todas las VMs
vagrant provision <nombre>      # Re-ejecutar script de aprovisionamiento

# ── Acceso SSH ────────────────────────────────────────────────────────────────
vagrant ssh router | infra | nfs | db | lb | web1 | web2 | servicios

# ── Verificación desde el host ─────────────────────────────────────────────────
curl http://localhost:8082               # Web balanceador
curl http://localhost:8081               # Web2 directo
curl http://localhost:8083/zabbix        # Zabbix
curl http://localhost:8083/fog/management # FOG

# ── Base de datos ─────────────────────────────────────────────────────────────
mysql -h 192.168.40.13 -u webuser -pWebPass@2024 mastertech

# ── Zabbix ────────────────────────────────────────────────────────────────────
vagrant ssh servicios
sudo bash /usr/local/bin/zabbix_add_hosts.sh

# ── Agente Zabbix en un nodo ────────────────────────────────────────────────────
bash scripts/ZABBIX_AGENT.sh <hostname> <ip>
```

---

## 📚 Tecnologías utilizadas

| Tecnología | Versión | Uso en el proyecto |
|-----------|---------|-------------------|
| **Debian Bookworm** | 12 | SO base de todas las VMs |
| **Vagrant** | ≥ 2.3 | Gestión y aprovisionamiento de VMs |
| **VirtualBox** | ≥ 6.1 | Hipervisor de virtualización |
| **Apache2** | 2.4 | Servidor web (web1, web2) |
| **PHP** | 8.2 | Lenguaje de la aplicación web |
| **MariaDB** | 10.x | Base de datos relacional |
| **HAProxy** | 2.x | Balanceador de carga round-robin |
| **NFS** | kernel | Almacenamiento web compartido |
| **bind9** | — | DNS con 3 zonas (red1, red2, red3) |
| **isc-dhcp-server** | — | DHCP red interna |
| **vsftpd** | — | Servidor FTP interno |
| **nftables** | — | NAT en el router |
| **iptables** | — | Cortafuegos en todos los nodos |
| **Zabbix** | 6.4.21 | Monitorización en tiempo real |
| **FOG Project** | latest | Despliegue PXE de imágenes |
| **Python 3** | 3.x | Administrador FTP + sistema de incidencias |
| **Tkinter** | stdlib | Interfaces gráficas Python |
| **Active Directory** | WS 2019 | Dominio MasterTech.lan |
| **PowerShell** | — | Automatización Windows Server |
| **Cisco Packet Tracer** | — | Topología de red simulada |

---

<div align="center">

**Mario Da Silva Ortega** · 2-ASIR · IES Albarregas · 2025-2026  
[🔗 github.com/mdasilvao01-code/Proyecto_Mastertech](https://github.com/mdasilvao01-code/Proyecto_Mastertech)

</div>
