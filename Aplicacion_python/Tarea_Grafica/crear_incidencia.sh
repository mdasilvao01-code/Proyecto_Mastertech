#!/bin/bash

#Carpeta donde se guardan las incidencias
CARPETA_INCIDENCIAS="incidencias"
mkdir -p "$CARPETA_INCIDENCIAS"

USUARIO="$1"
TITULO="$2"
DESCRIPCION="$3"

FECHA=$(date '+%Y-%m-%d %H:%M:%S')
NUMERO=$(ls "$CARPETA_INCIDENCIAS" | wc -l)
NUMERO=$((NUMERO + 1))

ARCHIVO="$CARPETA_INCIDENCIAS/INC_$NUMERO.txt"

echo "ID: $NUMERO" > "$ARCHIVO"
echo "Creada por: $USUARIO" >> "$ARCHIVO"
echo "Fecha: $FECHA" >> "$ARCHIVO"
echo "Estado: Abierta" >> "$ARCHIVO"
echo "Título: $TITULO" >> "$ARCHIVO"
echo "Descripción: $DESCRIPCION" >> "$ARCHIVO"

echo "$NUMERO"
