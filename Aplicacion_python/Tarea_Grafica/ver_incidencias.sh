#!/bin/bash

CARPETA_INCIDENCIAS="incidencias"
mkdir -p "$CARPETA_INCIDENCIAS"

if [ -z "$(ls -A "$CARPETA_INCIDENCIAS")" ]; then
    echo "No hay incidencias registradas"
else
    for ARCHIVO in $(ls "$CARPETA_INCIDENCIAS" | sort); do
        cat "$CARPETA_INCIDENCIAS/$ARCHIVO"
    done
fi
