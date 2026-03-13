import os
import tkinter as tk
from tkinter import ttk, messagebox, scrolledtext
from datetime import datetime

#Carpeta donde se almacenara las incidencias
CARPETA_INCIDENCIAS = "incidencias"

if not os.path.exists(CARPETA_INCIDENCIAS):
    os.mkdir(CARPETA_INCIDENCIAS)


#Clase incidencias para llamar a los objetos para el menu grafico
class SistemaIncidencias:
    def __init__(self, root):
        self.root = root
        self.root.title("Sistema de Gestión de Incidencias")
        self.root.geometry("800x600")
        self.root.configure(bg="#f0f0f0")
        
        self.usuario = None
        self.rol = None
        
        self.mostrar_login()
    
    #Funcion limpiar pantalla 
    def limpiar_ventana(self):
        for widget in self.root.winfo_children():
            widget.destroy()
    
    #Funcion para mostrar el login 
    def mostrar_login(self):
        self.limpiar_ventana()
        
        #Cuadro principal del menu
        frame = tk.Frame(self.root, bg="#f0f0f0")
        frame.place(relx=0.5, rely=0.5, anchor="center")
        
        #Titulo principal del menu
        titulo = tk.Label(frame, text="Sistema de Incidencias", 
                         font=("Arial", 24, "bold"), bg="#f0f0f0", fg="#333")
        titulo.pack(pady=20)
        
        #Parte para seleccionar el usuario para iniciar sesion
        tk.Label(frame, text="Nombre de usuario:", font=("Arial", 12), 
                bg="#f0f0f0").pack(pady=5)
        self.entry_usuario = tk.Entry(frame, font=("Arial", 12), width=25)
        self.entry_usuario.pack(pady=5)
        
        #Rol que desenpeña el usuario 
        tk.Label(frame, text="Selecciona tu rol:", font=("Arial", 12), 
                bg="#f0f0f0").pack(pady=5)
        self.combo_rol = ttk.Combobox(frame, values=["admin", "tecnico", "cliente"], 
                                      font=("Arial", 12), width=23, state="readonly")
        self.combo_rol.pack(pady=5)
        self.combo_rol.current(0)
        
        #Boton para iniciar sesion
        btn_ingresar = tk.Button(frame, text="Ingresar", font=("Arial", 12, "bold"),
                                bg="#4CAF50", fg="white", padx=30, pady=10,
                                command=self.ingresar)
        btn_ingresar.pack(pady=20)
    
    #Funcion ingresar que dependiendo de que usuario entre muestra una pantalla o otra
    def ingresar(self):
        self.usuario = self.entry_usuario.get().strip()
        self.rol = self.combo_rol.get()
        
        if not self.usuario:
            messagebox.showerror("Error", "Por favor ingresa un nombre de usuario")
            return
        
        if self.rol == "admin":
            self.mostrar_menu_admin()
        elif self.rol == "tecnico":
            self.mostrar_menu_tecnico()
        elif self.rol == "cliente":
            self.mostrar_menu_cliente()
    
    #Pantalla del administrador
    def mostrar_menu_admin(self):
        self.limpiar_ventana()
        
        #Cabecera 
        header = tk.Frame(self.root, bg="#2196F3", height=80)
        header.pack(fill="x")
        
        tk.Label(header, text=f"Administrador: {self.usuario}", 
                font=("Arial", 16, "bold"), bg="#2196F3", fg="white").pack(pady=10)
        
        tk.Button(header, text="Cerrar Sesión", command=self.mostrar_login,
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)
        
        #Botones principales
        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        
        tk.Button(frame_botones, text="📋 Ver Incidencias", 
                 font=("Arial", 14), bg="#4CAF50", fg="white",
                 padx=20, pady=15, width=20,
                 command=self.ver_incidencias).pack(pady=10)
        
        tk.Button(frame_botones, text="✏️ Modificar Incidencia", 
                 font=("Arial", 14), bg="#FF9800", fg="white",
                 padx=20, pady=15, width=20,
                 command=self.modificar_incidencia).pack(pady=10)
    
    #Funcion que muestra por pantalla que muestra el menu del tecnico
    def mostrar_menu_tecnico(self):
        self.limpiar_ventana()
        
        #Cabecera
        header = tk.Frame(self.root, bg="#9C27B0", height=80)
        header.pack(fill="x")
        
        tk.Label(header, text=f"Técnico: {self.usuario}", 
                font=("Arial", 16, "bold"), bg="#9C27B0", fg="white").pack(pady=10)
        
        tk.Button(header, text="Cerrar Sesión", command=self.mostrar_login,
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)
        
        #Botones principales
        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        
        tk.Button(frame_botones, text="📋 Ver Incidencias", 
                 font=("Arial", 14), bg="#4CAF50", fg="white",
                 padx=20, pady=15, width=20,
                 command=self.ver_incidencias).pack(pady=10)
    
    #Funcion para mostrar por pantalla la parte del menu de cliente
    def mostrar_menu_cliente(self):
        self.limpiar_ventana()
        
        #Cabecera
        header = tk.Frame(self.root, bg="#00BCD4", height=80)
        header.pack(fill="x")
        
        tk.Label(header, text=f"Cliente: {self.usuario}", 
                font=("Arial", 16, "bold"), bg="#00BCD4", fg="white").pack(pady=10)
        
        tk.Button(header, text="Cerrar Sesión", command=self.mostrar_login,
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)
        
        #Botones principales
        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        
        tk.Button(frame_botones, text="➕ Crear Nueva Incidencia", 
                 font=("Arial", 14), bg="#4CAF50", fg="white",
                 padx=20, pady=15, width=25,
                 command=self.crear_incidencia).pack(pady=10)
        
        tk.Button(frame_botones, text="📋 Ver Mis Incidencias", 
                 font=("Arial", 14), bg="#2196F3", fg="white",
                 padx=20, pady=15, width=25,
                 command=self.ver_incidencias).pack(pady=10)
    
    #Funcion para crear la incidencia y sus botones
    def crear_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Crear Nueva Incidencia")
        ventana.geometry("500x400")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        #Titulo
        tk.Label(frame, text="Título:", font=("Arial", 12), 
                bg="#f0f0f0").pack(anchor="w", pady=5)
        entry_titulo = tk.Entry(frame, font=("Arial", 11), width=50)
        entry_titulo.pack(pady=5)
        
        #Descripcion
        tk.Label(frame, text="Descripción:", font=("Arial", 12), 
                bg="#f0f0f0").pack(anchor="w", pady=5)
        text_descripcion = scrolledtext.ScrolledText(frame, font=("Arial", 11), 
                                                     width=50, height=10)
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
            
            nombre_archivo = f"INC_{numero}.txt"
            ruta = os.path.join(CARPETA_INCIDENCIAS, nombre_archivo)
            
            with open(ruta, "w", encoding="utf-8") as f:
                f.write(f"ID: {numero}\n")
                f.write(f"Creada por: {self.usuario}\n")
                f.write(f"Fecha: {fecha}\n")
                f.write("Estado: Abierta\n")
                f.write(f"Título: {titulo}\n")
                f.write(f"Descripción: {descripcion}\n")
            
            messagebox.showinfo("Éxito", f"Incidencia #{numero} creada correctamente")
            ventana.destroy()
        
        #Boton para guardar la incidencia
        tk.Button(frame, text="Guardar Incidencia", font=("Arial", 12, "bold"),
                 bg="#4CAF50", fg="white", padx=20, pady=10,
                 command=guardar).pack(pady=20)
    
    #Funcion para ver las incidencias y sus botones
    def ver_incidencias(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Lista de Incidencias")
        ventana.geometry("700x500")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        tk.Label(frame, text="Incidencias Registradas", font=("Arial", 16, "bold"),
                bg="#f0f0f0").pack(pady=10)
        
        #Parte para mostrar las incidencias
        text_area = scrolledtext.ScrolledText(frame, font=("Courier", 10), 
                                              width=80, height=25)
        text_area.pack(pady=10, fill="both", expand=True)
        
        archivos = sorted(os.listdir(CARPETA_INCIDENCIAS))
        
        if not archivos:
            text_area.insert("1.0", "No hay incidencias registradas.")
        else:
            for archivo in archivos:
                text_area.insert(tk.END, "="*70 + "\n")
                with open(os.path.join(CARPETA_INCIDENCIAS, archivo), "r", 
                         encoding="utf-8") as f:
                    text_area.insert(tk.END, f.read())
                text_area.insert(tk.END, "\n")
        
        text_area.config(state="disabled")
    
    #Funcion para modificar las incidencias ya creadas y configuarar los botones
    def modificar_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Modificar Incidencia")
        ventana.geometry("400x250")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        tk.Label(frame, text="ID de la incidencia:", font=("Arial", 12),
                bg="#f0f0f0").pack(pady=10)
        entry_id = tk.Entry(frame, font=("Arial", 12), width=20)
        entry_id.pack(pady=5)
        
        tk.Label(frame, text="Nuevo estado:", font=("Arial", 12),
                bg="#f0f0f0").pack(pady=10)
        combo_estado = ttk.Combobox(frame, values=["Abierta", "En proceso", "Cerrada"],
                                   font=("Arial", 12), width=18, state="readonly")
        combo_estado.pack(pady=5)
        combo_estado.current(0)
        
        #Funcion para actualizar cada cambio que haga el programa
        def actualizar():
            id_inc = entry_id.get().strip()
            nuevo_estado = combo_estado.get()
            
            if not id_inc:
                messagebox.showerror("Error", "Ingresa un ID")
                return
            
            ruta = os.path.join(CARPETA_INCIDENCIAS, f"INC_{id_inc}.txt")
            
            if not os.path.exists(ruta):
                messagebox.showerror("Error", "Incidencia no encontrada")
                return
            
            with open(ruta, "r", encoding="utf-8") as f:
                lineas = f.readlines()
            
            lineas[3] = f"Estado: {nuevo_estado}\n"
            
            with open(ruta, "w", encoding="utf-8") as f:
                f.writelines(lineas)
            
            messagebox.showinfo("Éxito", f"Incidencia #{id_inc} actualizada")
            ventana.destroy()
        
        tk.Button(frame, text="Actualizar", font=("Arial", 12, "bold"),
                 bg="#FF9800", fg="white", padx=30, pady=10,
                 command=actualizar).pack(pady=20)


#Muestra el menu
if __name__ == "__main__":
    root = tk.Tk()
    app = SistemaIncidencias(root)
    root.mainloop()