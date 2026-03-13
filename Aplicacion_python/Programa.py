import os
from datetime import datetime

#Obtener la ruta donde esta el archivo .py
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CARPETA_INCIDENCIAS = os.path.join(BASE_DIR, "incidencias")

#Crear carpeta si no existe
os.makedirs(CARPETA_INCIDENCIAS, exist_ok=True)

#Funcion para obtener el siguiente id de la incidencia
def obtener_siguiente_id():
    archivos = os.listdir(CARPETA_INCIDENCIAS)
    ids = []

    for archivo in archivos:
        if archivo.startswith("INC_") and archivo.endswith(".txt"):
            try:
                numero = int(archivo.split("_")[1].split(".")[0])
                ids.append(numero)
            except:
                pass

    if not ids:
        return 1

    return max(ids) + 1


#Funcion para crear la incidencia
def crear_incidencia(usuario):
    titulo = input("Titulo de la incidencia: ")
    descripcion = input("Descripcion: ")

    fecha = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    numero = obtener_siguiente_id()

    nombre_archivo = f"INC_{numero}.txt"
    ruta = os.path.join(CARPETA_INCIDENCIAS, nombre_archivo)

    with open(ruta, "w", encoding="utf-8") as f:
        f.write(f"ID: {numero}\n")
        f.write(f"Creada por: {usuario}\n")
        f.write(f"Fecha: {fecha}\n")
        f.write("Estado: Abierta\n")
        f.write(f"Título: {titulo}\n")
        f.write(f"Descripción: {descripcion}\n")

    print("Incidencia creada correctamente")


#Funcion para ver las incidencias
def ver_incidencias():
    archivos = sorted(os.listdir(CARPETA_INCIDENCIAS))

    if not archivos:
        print("No hay incidencias")
        return

    for archivo in archivos:
        print("\n-------------------------")
        with open(os.path.join(CARPETA_INCIDENCIAS, archivo), "r", encoding="utf-8") as f:
            print(f.read())


#Funcion para modificar las incidencias
def modificar_incidencia():
    id_inc = input("ID de la incidencia a modificar: ")
    ruta = os.path.join(CARPETA_INCIDENCIAS, f"INC_{id_inc}.txt")

    if not os.path.exists(ruta):
        print("Incidencia no encontrada.")
        return

    nuevo_estado = input("Nuevo estado (Abierta / En proceso / Cerrada): ")

    with open(ruta, "r", encoding="utf-8") as f:
        lineas = f.readlines()

    if len(lineas) >= 4:
        lineas[3] = f"Estado: {nuevo_estado}\n"

        with open(ruta, "w", encoding="utf-8") as f:
            f.writelines(lineas)

        print("Incidencia actualizada")
    else:
        print("Error en el archivo de incidencia")


#Menu del administrador
def menu_admin():
    while True:
        print("\n--- MENU ADMINISTRADOR ---")
        print("1.Ver incidencias")
        print("2.Modificar incidencia")
        print("3.Salir")
        opcion = input("Opcion: ")

        if opcion == "1":
            ver_incidencias()
        elif opcion == "2":
            modificar_incidencia()
        elif opcion == "3":
            break


#Menu del tecnico
def menu_tecnico():
    while True:
        print("\n--- MENU TECNICO ---")
        print("1.Ver incidencias")
        print("2.Salir")
        opcion = input("Opcion: ")

        if opcion == "1":
            ver_incidencias()
        elif opcion == "2":
            break


#Menu del usuario
def menu_cliente(usuario):
    while True:
        print("\n--- MENU CLIENTE ---")
        print("1.Crear incidencia")
        print("2.Salir")
        opcion = input("Opcion: ")

        if opcion == "1":
            crear_incidencia(usuario)
        elif opcion == "2":
            break

#Menu principal
def main():
    print("=== SISTEMA DE INCIDENCIAS ===")
    usuario = input("Nombre de usuario: ")
    rol = input("Rol (admin / tecnico / cliente): ").lower()

    if rol == "admin":
        menu_admin()
    elif rol == "tecnico":
        menu_tecnico()
    elif rol == "cliente":
        menu_cliente(usuario)
    else:
        print("Rol no válido")


#Ejecuta el menu
if __name__ == "__main__":
    main()