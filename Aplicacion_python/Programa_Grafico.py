import os
import tkinter as tk
from tkinter import ttk, messagebox, scrolledtext
from datetime import datetime

# Carpeta de incidencias
CARPETA_INCIDENCIAS = "incidencias"
if not os.path.exists(CARPETA_INCIDENCIAS):
    os.mkdir(CARPETA_INCIDENCIAS)

# Usuarios con rol
USUARIOS = {
    "admin": {"password": "1234", "rol": "admin"},
    "tec1": {"password": "1234", "rol": "tecnico"},
    "cliente1": {"password": "1234", "rol": "cliente"}
}

class SistemaIncidencias:
    def __init__(self, root):
        self.root = root
        self.root.title("Sistema de Gestion de Incidencias")
        self.root.geometry("800x600")
        self.root.configure(bg="#f0f0f0")
        self.usuario = None
        self.rol = None
        self.mostrar_login()

    def limpiar_ventana(self):
        for widget in self.root.winfo_children():
            widget.destroy()

    # ---------------- LOGIN ----------------
    def mostrar_login(self):
        self.limpiar_ventana()
        frame = tk.Frame(self.root, bg="#f0f0f0")
        frame.place(relx=0.5, rely=0.5, anchor="center")

        tk.Label(frame, text="Sistema de Incidencias", font=("Arial", 24, "bold"),
                 bg="#f0f0f0", fg="#333").pack(pady=20)
        tk.Label(frame, text="Usuario:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        self.entry_usuario = tk.Entry(frame, font=("Arial", 12), width=25)
        self.entry_usuario.pack(pady=5)

        tk.Label(frame, text="Contrasena:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        self.entry_password = tk.Entry(frame, font=("Arial", 12), width=25, show="*")
        self.entry_password.pack(pady=5)

        tk.Button(frame, text="Ingresar", font=("Arial", 12, "bold"),
                  bg="#4CAF50", fg="white", padx=30, pady=10,
                  command=self.ingresar).pack(pady=20)

    def ingresar(self):
        usuario = self.entry_usuario.get().strip()
        password = self.entry_password.get().strip()

        if usuario not in USUARIOS or USUARIOS[usuario]["password"] != password:
            messagebox.showerror("Error", "Usuario o contrasena incorrectos")
            return

        self.usuario = usuario
        self.rol = USUARIOS[usuario]["rol"]

        if self.rol == "admin":
            self.mostrar_menu_admin()
        elif self.rol == "tecnico":
            self.mostrar_menu_tecnico()
        else:
            self.mostrar_menu_cliente()

    # ---------------- MENUS ----------------
    def mostrar_menu_admin(self):
        self.limpiar_ventana()
        header = tk.Frame(self.root, bg="#2196F3", height=80)
        header.pack(fill="x")
        tk.Label(header, text=f"Administrador: {self.usuario}", font=("Arial", 16, "bold"),
                 bg="#2196F3", fg="white").pack(pady=10)
        tk.Button(header, text="Cerrar Sesion", command=self.mostrar_login,
                  bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)

        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)

        tk.Button(frame_botones, text="Ver Incidencias", font=("Arial", 14),
                  bg="#4CAF50", fg="white", padx=20, pady=15, width=20,
                  command=self.ver_incidencias).pack(pady=10)
        tk.Button(frame_botones, text="Modificar Incidencia", font=("Arial", 14),
                  bg="#FF9800", fg="white", padx=20, pady=15, width=20,
                  command=self.modificar_incidencia).pack(pady=10)
        tk.Button(frame_botones, text="Asignar Tecnico", font=("Arial", 14),
                  bg="#9C27B0", fg="white", padx=20, pady=15, width=20,
                  command=self.asignar_tecnico).pack(pady=10)

    def mostrar_menu_tecnico(self):
        self.limpiar_ventana()
        header = tk.Frame(self.root, bg="#9C27B0", height=80)
        header.pack(fill="x")
        tk.Label(header, text=f"Tecnico: {self.usuario}", font=("Arial", 16, "bold"),
                 bg="#9C27B0", fg="white").pack(pady=10)
        tk.Button(header, text="Cerrar Sesion", command=self.mostrar_login,
                  bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)

        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        tk.Button(frame_botones, text="Ver Mis Incidencias", font=("Arial", 14),
                  bg="#4CAF50", fg="white", padx=20, pady=15, width=25,
                  command=self.ver_incidencias).pack(pady=10)

    def mostrar_menu_cliente(self):
        self.limpiar_ventana()
        header = tk.Frame(self.root, bg="#00BCD4", height=80)
        header.pack(fill="x")
        tk.Label(header, text=f"Cliente: {self.usuario}", font=("Arial", 16, "bold"),
                 bg="#00BCD4", fg="white").pack(pady=10)
        tk.Button(header, text="Cerrar Sesion", command=self.mostrar_login,
                  bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)

        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        tk.Button(frame_botones, text="Crear Nueva Incidencia", font=("Arial", 14),
                  bg="#4CAF50", fg="white", padx=20, pady=15, width=25,
                  command=self.crear_incidencia).pack(pady=10)
        tk.Button(frame_botones, text="Ver Mis Incidencias", font=("Arial", 14),
                  bg="#2196F3", fg="white", padx=20, pady=15, width=25,
                  command=self.ver_incidencias).pack(pady=10)

    # ---------------- CREAR INCIDENCIA ----------------
    def crear_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Crear Nueva Incidencia")
        ventana.geometry("500x400")
        ventana.configure(bg="#f0f0f0")
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)

        tk.Label(frame, text="Titulo:", font=("Arial", 12), bg="#f0f0f0").pack(anchor="w", pady=5)
        entry_titulo = tk.Entry(frame, font=("Arial", 11), width=50)
        entry_titulo.pack(pady=5)

        tk.Label(frame, text="Descripcion:", font=("Arial", 12), bg="#f0f0f0").pack(anchor="w", pady=5)
        text_descripcion = scrolledtext.ScrolledText(frame, font=("Arial", 11), width=50, height=10)
        text_descripcion.pack(pady=5)

        def guardar():
            titulo = entry_titulo.get().strip()
            descripcion = text_descripcion.get("1.0", tk.END).strip()
            if not titulo or not descripcion:
                messagebox.showerror("Error", "Completa todos los campos")
                return
            fecha = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            archivos = os.listdir(CARPETA_INCIDENCIAS)
            numero = len(archivos) + 1
            ruta = os.path.join(CARPETA_INCIDENCIAS, f"INC_{numero}.txt")
            with open(ruta, "w", encoding="utf-8") as f:
                f.write(f"ID: {numero}\n")
                f.write(f"Creada por: {self.usuario}\n")
                f.write(f"Fecha: {fecha}\n")
                f.write("Estado: Abierta\n")
                f.write("Tecnico: Sin asignar\n")
                f.write(f"Titulo: {titulo}\n")
                f.write(f"Descripcion: {descripcion}\n")
            messagebox.showinfo("Exito", f"Incidencia #{numero} creada correctamente")
            ventana.destroy()

        tk.Button(frame, text="Guardar Incidencia", font=("Arial", 12, "bold"),
                  bg="#4CAF50", fg="white", padx=20, pady=10, command=guardar).pack(pady=20)

    # ---------------- VER INCIDENCIAS ----------------
    def ver_incidencias(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Lista de Incidencias")
        ventana.geometry("700x500")
        ventana.configure(bg="#f0f0f0")
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)

        tk.Label(frame, text="Incidencias Registradas", font=("Arial", 16, "bold"),
                 bg="#f0f0f0").pack(pady=10)

        text_area = scrolledtext.ScrolledText(frame, font=("Courier", 10), width=80, height=25)
        text_area.pack(pady=10, fill="both", expand=True)

        archivos = sorted(os.listdir(CARPETA_INCIDENCIAS))
        if not archivos:
            text_area.insert("1.0", "No hay incidencias registradas.")
        else:
            for archivo in archivos:
                ruta = os.path.join(CARPETA_INCIDENCIAS, archivo)
                with open(ruta, "r", encoding="utf-8") as f:
                    contenido = f.read()
                if self.rol == "cliente" and f"Creada por: {self.usuario}" not in contenido:
                    continue
                if self.rol == "tecnico" and f"Tecnico: {self.usuario}" not in contenido:
                    continue
                text_area.insert(tk.END, "="*70 + "\n")
                text_area.insert(tk.END, contenido + "\n")
        text_area.config(state="disabled")

    # ---------------- MODIFICAR INCIDENCIA ----------------
    def modificar_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Modificar Incidencia")
        ventana.geometry("400x300")
        ventana.configure(bg="#f0f0f0")
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)

        tk.Label(frame, text="Selecciona ID:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        archivos = sorted(os.listdir(CARPETA_INCIDENCIAS))
        ids = [f.split("_")[1].split(".")[0] for f in archivos if f.startswith("INC_")]
        combo_id = ttk.Combobox(frame, values=ids, state="readonly")
        combo_id.pack(pady=5)

        tk.Label(frame, text="Nuevo estado:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        combo_estado = ttk.Combobox(frame, values=["Abierta", "En proceso", "Cerrada"], state="readonly")
        combo_estado.pack(pady=5)
        combo_estado.current(0)

        tk.Label(frame, text="Comentario opcional:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        entry_comentario = tk.Entry(frame, font=("Arial", 12), width=30)
        entry_comentario.pack(pady=5)

        def actualizar():
            if not combo_id.get():
                messagebox.showerror("Error", "Selecciona un ID")
                return
            id_inc = combo_id.get()
            ruta = os.path.join(CARPETA_INCIDENCIAS, f"INC_{id_inc}.txt")
            if not os.path.exists(ruta):
                messagebox.showerror("Error", "Archivo no encontrado")
                return
            with open(ruta, "r", encoding="utf-8") as f:
                lineas = f.readlines()
            for i, linea in enumerate(lineas):
                if linea.startswith("Estado:"):
                    lineas[i] = f"Estado: {combo_estado.get()}\n"
            comentario = entry_comentario.get().strip()
            if comentario:
                lineas.append(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')} - Comentario: {comentario}\n")
            with open(ruta, "w", encoding="utf-8") as f:
                f.writelines(lineas)
            messagebox.showinfo("Exito", f"Incidencia #{id_inc} actualizada")
            ventana.destroy()

        tk.Button(frame, text="Actualizar", font=("Arial", 12, "bold"),
                  bg="#FF9800", fg="white", padx=30, pady=10, command=actualizar).pack(pady=20)

    # ---------------- ASIGNAR TECNICO ----------------
    def asignar_tecnico(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Asignar Tecnico")
        ventana.geometry("400x250")
        ventana.configure(bg="#f0f0f0")
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)

        tk.Label(frame, text="Selecciona ID:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        archivos = sorted(os.listdir(CARPETA_INCIDENCIAS))
        ids = [f.split("_")[1].split(".")[0] for f in archivos if f.startswith("INC_")]
        combo_id = ttk.Combobox(frame, values=ids, state="readonly")
        combo_id.pack(pady=5)

        tk.Label(frame, text="Selecciona tecnico:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        tecnicos = [u for u, info in USUARIOS.items() if info["rol"] == "tecnico"]
        combo_tecnico = ttk.Combobox(frame, values=tecnicos, state="readonly")
        combo_tecnico.pack(pady=5)

        def asignar():
            if not combo_id.get() or not combo_tecnico.get():
                messagebox.showerror("Error", "Completa todos los campos")
                return
            id_inc = combo_id.get()
            tecnico = combo_tecnico.get()
            ruta = os.path.join(CARPETA_INCIDENCIAS, f"INC_{id_inc}.txt")
            if not os.path.exists(ruta):
                messagebox.showerror("Error", "Archivo no encontrado")
                return
            with open(ruta, "r", encoding="utf-8") as f:
                lineas = f.readlines()
            for i, linea in enumerate(lineas):
                if linea.startswith("Tecnico:"):
                    lineas[i] = f"Tecnico: {tecnico}\n"
                    break
            with open(ruta, "w", encoding="utf-8") as f:
                f.writelines(lineas)
            messagebox.showinfo("Exito", f"Tecnico {tecnico} asignado a incidencia #{id_inc}")
            ventana.destroy()

        tk.Button(frame, text="Asignar", font=("Arial", 12, "bold"),
                  bg="#9C27B0", fg="white", padx=30, pady=10, command=asignar).pack(pady=20)

# ---------------- INICIO ----------------
if __name__ == "__main__":
    root = tk.Tk()
    app = SistemaIncidencias(root)
    root.mainloop()