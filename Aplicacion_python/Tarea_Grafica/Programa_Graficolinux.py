import tkinter as tk
from tkinter import ttk, messagebox, scrolledtext
import subprocess

class SistemaIncidencias:
    def __init__(self, root):
        self.root = root
        self.root.title("Sistema de Gestión de Incidencias")
        self.root.geometry("800x600")
        self.root.configure(bg="#f0f0f0")
        
        self.usuario = None
        self.rol = None
        
        self.mostrar_login()
    
    def limpiar_ventana(self):
        for widget in self.root.winfo_children():
            widget.destroy()
    
    def mostrar_login(self):
        self.limpiar_ventana()
        frame = tk.Frame(self.root, bg="#f0f0f0")
        frame.place(relx=0.5, rely=0.5, anchor="center")
        
        tk.Label(frame, text="Sistema de Incidencias", font=("Arial", 24, "bold"),
                 bg="#f0f0f0").pack(pady=20)
        
        tk.Label(frame, text="Nombre de usuario:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        self.entry_usuario = tk.Entry(frame, font=("Arial", 12), width=25)
        self.entry_usuario.pack(pady=5)
        
        tk.Label(frame, text="Selecciona tu rol:", font=("Arial", 12), bg="#f0f0f0").pack(pady=5)
        self.combo_rol = ttk.Combobox(frame, values=["admin", "tecnico", "cliente"], font=("Arial", 12),
                                      width=23, state="readonly")
        self.combo_rol.pack(pady=5)
        self.combo_rol.current(0)
        
        tk.Button(frame, text="Ingresar", font=("Arial", 12, "bold"), bg="#4CAF50", fg="white",
                  padx=30, pady=10, command=self.ingresar).pack(pady=20)
    
    def ingresar(self):
        self.usuario = self.entry_usuario.get().strip()
        self.rol = self.combo_rol.get()
        
        if not self.usuario:
            messagebox.showerror("Por favor ingresa un nombre de usuario")
            return
        
        if self.rol == "admin":
            self.mostrar_menu_admin()
        elif self.rol == "tecnico":
            self.mostrar_menu_tecnico()
        elif self.rol == "cliente":
            self.mostrar_menu_cliente()
    
    def mostrar_menu_admin(self):
        self.limpiar_ventana()
        header = tk.Frame(self.root, bg="#2196F3", height=80)
        header.pack(fill="x")
        tk.Label(header, text=f"Administrador: {self.usuario}", font=("Arial", 16, "bold"),
                 bg="#2196F3", fg="white").pack(pady=10)
        tk.Button(header, text="Cerrar Sesión", command=self.mostrar_login,
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)
        
        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        
        tk.Button(frame_botones, text="Ver Incidencias", font=("Arial", 14), bg="#4CAF50",
                  fg="white", padx=20, pady=15, width=20,
                  command=self.ver_incidencias).pack(pady=10)
        tk.Button(frame_botones, text="Modificar Incidencia", font=("Arial", 14), bg="#FF9800",
                  fg="white", padx=20, pady=15, width=20,
                  command=self.modificar_incidencia).pack(pady=10)
    
    def mostrar_menu_tecnico(self):
        self.mostrar_menu_admin()
    
    def mostrar_menu_cliente(self):
        self.limpiar_ventana()
        header = tk.Frame(self.root, bg="#00BCD4", height=80)
        header.pack(fill="x")
        tk.Label(header, text=f"Cliente: {self.usuario}", font=("Arial", 16, "bold"),
                 bg="#00BCD4", fg="white").pack(pady=10)
        tk.Button(header, text="Cerrar Sesión", command=self.mostrar_login,
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(pady=5)
        
        frame_botones = tk.Frame(self.root, bg="#f0f0f0")
        frame_botones.pack(pady=20)
        
        tk.Button(frame_botones, text="Crear Nueva Incidencia", font=("Arial", 14), bg="#4CAF50",
                  fg="white", padx=20, pady=15, width=25,
                  command=self.crear_incidencia).pack(pady=10)
        tk.Button(frame_botones, text="Ver Mis Incidencias", font=("Arial", 14), bg="#2196F3",
                  fg="white", padx=20, pady=15, width=25,
                  command=self.ver_incidencias).pack(pady=10)
    
    def crear_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Crear Nueva Incidencia")
        ventana.geometry("500x400")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        tk.Label(frame, text="Título:", font=("Arial", 12), bg="#f0f0f0").pack(anchor="w", pady=5)
        entry_titulo = tk.Entry(frame, font=("Arial", 11), width=50)
        entry_titulo.pack(pady=5)
        
        tk.Label(frame, text="Descripción:", font=("Arial", 12), bg="#f0f0f0").pack(anchor="w", pady=5)
        text_descripcion = scrolledtext.ScrolledText(frame, font=("Arial", 11), width=50, height=10)
        text_descripcion.pack(pady=5)
        
        def guardar():
            titulo = entry_titulo.get().strip()
            descripcion = text_descripcion.get("1.0", tk.END).strip()
            
            if not titulo or not descripcion:
                messagebox.showerror("Completa todos los campos")
                return
            
            resultado = subprocess.run(
                ["./crear_incidencia.sh", self.usuario, titulo, descripcion],
                capture_output=True, text=True
            )
            
            if resultado.returncode == 0:
                messagebox.showinfo("Éxito", f"Incidencia #{resultado.stdout.strip()} creada correctamente")
                ventana.destroy()
            else:
                messagebox.showerror(resultado.stderr)
        
        tk.Button(frame, text="Guardar Incidencia", font=("Arial", 12, "bold"),
                 bg="#4CAF50", fg="white", padx=20, pady=10,
                 command=guardar).pack(pady=20)
    
    def ver_incidencias(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Lista de Incidencias")
        ventana.geometry("700x500")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        tk.Label(frame, text="Incidencias Registradas", font=("Arial", 16, "bold"),
                bg="#f0f0f0").pack(pady=10)
        
        text_area = scrolledtext.ScrolledText(frame, font=("Courier", 10),
                                              width=80, height=25)
        text_area.pack(pady=10, fill="both", expand=True)
        
        resultado = subprocess.run(["./ver_incidencias.sh"], capture_output=True, text=True)
        text_area.insert("1.0", resultado.stdout)
        text_area.config(state="disabled")
    
    def modificar_incidencia(self):
        ventana = tk.Toplevel(self.root)
        ventana.title("Modificar Incidencia")
        ventana.geometry("400x250")
        ventana.configure(bg="#f0f0f0")
        
        frame = tk.Frame(ventana, bg="#f0f0f0")
        frame.pack(padx=20, pady=20, fill="both", expand=True)
        
        tk.Label(frame, text="ID de la incidencia:", font=("Arial", 12), bg="#f0f0f0").pack(pady=10)
        entry_id = tk.Entry(frame, font=("Arial", 12), width=20)
        entry_id.pack(pady=5)
        
        tk.Label(frame, text="Nuevo estado:", font=("Arial", 12), bg="#f0f0f0").pack(pady=10)
        combo_estado = ttk.Combobox(frame, values=["Abierta", "En proceso", "Cerrada"],
                                   font=("Arial", 12), width=18, state="readonly")
        combo_estado.pack(pady=5)
        combo_estado.current(0)
        
        def actualizar():
            id_inc = entry_id.get().strip()
            nuevo_estado = combo_estado.get()
            
            if not id_inc:
                messagebox.showerror("Ingresa un ID")
                return
            
            resultado = subprocess.run(["./modificar_incidencia.sh", id_inc, nuevo_estado],
                                       capture_output=True, text=True)
            
            if resultado.returncode == 0:
                messagebox.showinfo(resultado.stdout)
                ventana.destroy()
            else:
                messagebox.showerror(resultado.stdout)
        
        tk.Button(frame, text="Actualizar", font=("Arial", 12, "bold"),
                 bg="#FF9800", fg="white", padx=30, pady=10,
                 command=actualizar).pack(pady=20)

if __name__ == "__main__":
    root = tk.Tk()
    app = SistemaIncidencias(root)
    root.mainloop()
