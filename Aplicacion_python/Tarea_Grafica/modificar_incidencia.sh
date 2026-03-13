#!/bin/bash

CARPETA_INCIDENCIAS="incidencias"
ID="$1"
NUEVO_ESTADO="$2"

ARCHIVO="$CARPETA_INCIDENCIAS/INC_$ID.txt"

if [ ! -f "$ARCHIVO" ]; then
    echo "Incidencia no encontrada"
    exit 1
fi

# Reescribe la línea del estado
sed -i "4s/.*/Estado: $NUEVO_ESTADO/" "$ARCHIVO"
echo "Incidencia #$ID actualizada a estado '$NUEVO_ESTADO'"
