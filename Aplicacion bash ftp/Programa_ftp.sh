#!/bin/bash

ACTION="$1"
USER="$2"
PATH_ARG="$3"
PERM="$4"
LOG_FILE="${5:-/home/infra/ftp_admin.log}"

#Crear directorio y fichero de log si no existen
LOG_DIR="$(dirname "$LOG_FILE")"
sudo mkdir -p "$LOG_DIR"          2>/dev/null
sudo touch    "$LOG_FILE"         2>/dev/null
sudo chmod    666 "$LOG_FILE"     2>/dev/null

log() {
    local nivel="$1"
    local msg="$2"
    local ts
    ts=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$ts] [$nivel] [ACCION:${ACTION:-N/A}] [USER:${USER:-N/A}] [RUTA:${PATH_ARG:-N/A}] [PERM:${PERM:-N/A}] $msg" \
        >> "$LOG_FILE" 2>/dev/null
}

#Comprueba si a metido un parametro
if [ -z "$ACTION" ]; then
    echo "No se recibio accion"
    log "Script llamado sin accion"
    exit 1
fi


case "$ACTION" in

creador_usuarios)
    [ -z "$USER" ] && { echo "Falta usuario"; log "Crear usuario sin nombre"; exit 1; }

    if id "$USER" &>/dev/null; then
        echo "El usuario '$USER' ya existe"
        log "Usuario '$USER' ya existia"
        exit 0
    fi

    OUT=$(sudo useradd -m -s /bin/bash "$USER" 2>&1)
    CODE=$?
    if [ $CODE -eq 0 ]; then
        echo "Usuario '$USER' creado  (home: /home/$USER)"
        log "Usuario '$USER' creado"
    else
        echo "No se pudo crear '$USER'"
        echo "$OUT"
        log "Useradd '$USER' fallo: $OUT"
        exit 1
    fi
    ;;

#Crea la carpeta y el usuario ala vez
creador_usuarios_carpeta)
    [ -z "$USER"     ] && { echo "Falta usuario";  log "Crear usuario+carpeta sin usuario"; exit 1; }
    [ -z "$PATH_ARG" ] && { echo "Falta ruta";     log "Crear usuario+carpeta sin ruta";    exit 1; }

    # 1) Crear usuario si no existe
    if id "$USER" &>/dev/null; then
        echo "El usuario '$USER' ya existe"
        log "Usuario '$USER' ya existia"
    else
        echo "Creando usuario '$USER'..."
        OUT=$(sudo useradd -m -s /bin/bash "$USER" 2>&1)
        CODE=$?
        if [ $CODE -eq 0 ]; then
            echo "Usuario '$USER' creado"
            log "Usuario '$USER' creado"
        else
            echo "No se pudo crear el usuario '$USER'"
            echo "$OUT"
            log "useradd '$USER' fallo: $OUT"
            exit 1
        fi
    fi

    #Verifica la ruta
    if [[ "$PATH_ARG" = /* ]]; then
        CARPETA="$PATH_ARG"
    else
        CARPETA="/home/$USER/$PATH_ARG"
    fi

    #Crea la carpeta
    echo "Creando carpeta '$CARPETA'"
    OUT2=$(sudo mkdir -p "$CARPETA" 2>&1)
    CODE2=$?
    if [ $CODE2 -eq 0 ]; then
        sudo chown -R "$USER:$USER" "$CARPETA" 2>/dev/null
        echo "Carpeta '$CARPETA' creada y asignada a '$USER'"
        log "Carpeta '$CARPETA' creada y asignada a '$USER'"
    else
        echo "No se pudo crear '$CARPETA'"
        echo "$OUT2"
        log "mkdir '$CARPETA' fallo: $OUT2"
        exit 1
    fi
    ;;

#Borra el usuario
borrar_usuarios)
    [ -z "$USER" ] && { echo "Falta usuario"; log "Borrar usuario sin nombre"; exit 1; }

    if ! id "$USER" &>/dev/null; then
        echo "El usuario '$USER' no existe"
        log "Intento de borrar usuario '$USER' inexistente"
        exit 0
    fi

    OUT=$(sudo userdel -r "$USER" 2>&1)
    CODE=$?
    if [ $CODE -eq 0 ]; then
        echo "Usuario '$USER' eliminado (home borrado)"
        log "Usuario '$USER' eliminado"
    else
        echo "No se pudo eliminar '$USER'"
        echo "$OUT"
        log "userdel '$USER' fallo: $OUT"
        exit 1
    fi
    ;;

#Crea la carpeta
crear_carpetas)
    [ -z "$PATH_ARG" ] && { echo "Falta ruta"; log "Crear carpeta sin ruta"; exit 1; }

    OUT=$(sudo mkdir -p "$PATH_ARG" 2>&1)
    if [ $? -eq 0 ]; then
        echo "Carpeta '$PATH_ARG' creada"
        log "OK" "carpeta '$PATH_ARG' creada"
    else
        echo "No se pudo crear '$PATH_ARG'"
        echo "$OUT"
        log "mkdir '$PATH_ARG' fallo: $OUT"
        exit 1
    fi
    ;;

#Borrar carpeta
borrar_carpetas)
    [ -z "$PATH_ARG" ] && { echo "Falta ruta"; log "Borrar carpeta sin ruta"; exit 1; }

    #Proteccion contra rutas criticas
    for PROT in "/" "/etc" "/bin" "/usr" "/var" "/root" "/boot" "/lib" "/sys" "/proc" "/home"; do
        if [ "$PATH_ARG" = "$PROT" ]; then
            echo "Ruta protegida '$PATH_ARG', no se puede borrar"
            log "Intento de borrar ruta protegida '$PATH_ARG'"
            exit 1
        fi
    done

    OUT=$(sudo rm -rf "$PATH_ARG" 2>&1)
    if [ $? -eq 0 ]; then
        echo "Carpeta '$PATH_ARG' eliminada"
        log "Carpeta '$PATH_ARG' eliminada"
    else
        echo "No se pudo eliminar '$PATH_ARG'"
        echo "$OUT"
        log "rm '$PATH_ARG' fallo: $OUT"
        exit 1
    fi
    ;;

#Cambia los permisos
dar_permisos)
    [ -z "$PATH_ARG" ] && { echo "Falta ruta";     log "chmod sin ruta";    exit 1; }
    [ -z "$PERM"     ] && { echo "Faltan permisos"; log "chmod sin permisos"; exit 1; }

    OUT=$(sudo chmod "$PERM" "$PATH_ARG" 2>&1)
    if [ $? -eq 0 ]; then
        echo "Permisos '$PERM' aplicados a '$PATH_ARG'"
        log "chmod $PERM en '$PATH_ARG'"
    else
        echo "No se pudieron aplicar permisos"
        echo "$OUT"
        log "chmod '$PERM' en '$PATH_ARG' fallo: $OUT"
        exit 1
    fi
    ;;

#Cabia de propietario la carpeta o archivo
cambiar_propietario)
    [ -z "$USER"     ] && { echo "Falta usuario"; log "chown sin usuario"; exit 1; }
    [ -z "$PATH_ARG" ] && { echo "Falta ruta";    log "chown sin ruta";    exit 1; }

    OUT=$(sudo chown -R "$USER:$USER" "$PATH_ARG" 2>&1)
    if [ $? -eq 0 ]; then
        echo "Propietario de '$PATH_ARG' cambiado a '$USER'"
        log "chown '$USER' en '$PATH_ARG'"
    else
        echo "No se pudo cambiar el propietario"
        echo "$OUT"
        log "chown '$USER' en '$PATH_ARG' fallo: $OUT"
        exit 1
    fi
    ;;

#Lista el contenido deuna ruta
listar)
    [ -z "$PATH_ARG" ] && { echo "Falta ruta"; log "Listar sin ruta"; exit 1; }

    if [ ! -e "$PATH_ARG" ]; then
        echo "La ruta '$PATH_ARG' no existe"
        log "Listar ruta inexistente '$PATH_ARG'"
        exit 1
    fi

    log "listado de '$PATH_ARG'"
    echo "Contenido de: $PATH_ARG"
    sudo ls -lah "$PATH_ARG"
    ;;

#En caso de que una opcion no este en el programa dara un error
*)
    echo "Accion desconocida '$ACTION'"
    echo "Acciones validas: creador_usuarios, creador_usuarios_carpeta, borrar_usuarios,"
    echo "crear_carpetas, borrar_carpetas, dar_permisos, cambiar_propietario, listar"
    log "accion desconocida '$ACTION'"
    exit 1
    ;;
esac

exit 0