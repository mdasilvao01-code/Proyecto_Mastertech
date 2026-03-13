#!/usr/bin/env python3
import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox
import subprocess
from datetime import datetime

SERVER   = "infra@192.168.10.10"
SCRIPT   = "/home/infra/Programa_ftp.sh"
LOG_FILE = "/home/infra/ftp_admin.log"


class FTPAdmin:
    def __init__(self, root):
        self.root = root
        self.root.title("Administrador FTP")
        self.root.geometry("720x620")
        self.root.resizable(True, True)
        self._crear_interfaz()

    #Interfaz
    def _crear_interfaz(self):
        main = ttk.Frame(self.root, padding="20")
        main.grid(row=0, column=0, sticky="nsew")
        self.root.columnconfigure(0, weight=1)
        self.root.rowconfigure(0, weight=1)
        main.columnconfigure(1, weight=1)

        ttk.Label(main, text="Administrador FTP", font=("Arial", 16, "bold")).grid(
            row=0, column=0, columnspan=2, pady=(0, 15)
        )

        #Campos
        ttk.Label(main, text="Usuario:").grid(row=1, column=0, sticky="w", pady=4)
        self.entry_user = ttk.Entry(main, width=50)
        self.entry_user.grid(row=1, column=1, sticky="ew", pady=4)

        ttk.Label(main, text="Ruta / Carpeta:").grid(row=2, column=0, sticky="w", pady=4)
        self.entry_path = ttk.Entry(main, width=50)
        self.entry_path.grid(row=2, column=1, sticky="ew", pady=4)
        ttk.Label(
            main,
            text="  Ruta absoluta (/home/user/carpeta) o nombre de carpeta (se crea en /home/usuario/)",
            font=("Arial", 8), foreground="gray"
        ).grid(row=3, column=1, sticky="w")

        ttk.Label(main, text="Permisos:").grid(row=4, column=0, sticky="w", pady=4)
        self.entry_perm = ttk.Entry(main, width=50)
        self.entry_perm.grid(row=4, column=1, sticky="ew", pady=4)
        self.entry_perm.insert(0, "755")

        #Seccion Usuarios
        fu = ttk.LabelFrame(main, text="Usuarios", padding="8")
        fu.grid(row=5, column=0, columnspan=2, sticky="ew", pady=6)
        for i in range(3):
            fu.columnconfigure(i, weight=1)
        ttk.Button(fu, text="Crear Usuario",           command=self.crear_usuario).grid(row=0, column=0, padx=4, sticky="ew")
        ttk.Button(fu, text="Crear Usuario + Carpeta", command=self.crear_usuario_carpeta).grid(row=0, column=1, padx=4, sticky="ew")
        ttk.Button(fu, text="Borrar Usuario",          command=self.borrar_usuario).grid(row=0, column=2, padx=4, sticky="ew")

        #Seccion Carpetas
        fc = ttk.LabelFrame(main, text="Carpetas", padding="8")
        fc.grid(row=6, column=0, columnspan=2, sticky="ew", pady=6)
        for i in range(2):
            fc.columnconfigure(i, weight=1)
        ttk.Button(fc, text="Crear Carpeta", command=self.crear_carpeta).grid(row=0, column=0, padx=4, sticky="ew")
        ttk.Button(fc, text="Borrar Carpeta", command=self.borrar_carpeta).grid(row=0, column=1, padx=4, sticky="ew")

        #Seccion Permisos
        fp = ttk.LabelFrame(main, text="Permisos y Propietario", padding="8")
        fp.grid(row=7, column=0, columnspan=2, sticky="ew", pady=6)
        for i in range(3):
            fp.columnconfigure(i, weight=1)
        ttk.Button(fp, text="Cambiar Permisos",    command=self.permisos).grid(row=0, column=0, padx=4, sticky="ew")
        ttk.Button(fp, text="Cambiar Propietario", command=self.propietario).grid(row=0, column=1, padx=4, sticky="ew")
        ttk.Button(fp, text="Listar",              command=self.listar).grid(row=0, column=2, padx=4, sticky="ew")

        #Salida
        ttk.Label(main, text="Salida:", font=("Arial", 10, "bold")).grid(
            row=8, column=0, columnspan=2, sticky="w", pady=(10, 3)
        )
        self.salida = scrolledtext.ScrolledText(main, width=70, height=10, font=("Consolas", 9))
        self.salida.grid(row=9, column=0, columnspan=2, sticky="nsew", pady=(0, 8))
        main.rowconfigure(9, weight=1)

        #Botones inferiores
        fb = ttk.Frame(main)
        fb.grid(row=10, column=0, columnspan=2, pady=(0, 4))
        ttk.Button(fb, text="Limpiar Salida",   command=lambda: self.salida.delete(1.0, tk.END)).grid(row=0, column=0, padx=10)
        ttk.Button(fb, text="Ver Log Completo", command=self.ver_log).grid(row=0, column=1, padx=10)

    #SSH
    def _ssh(self, action, user="", path="", perm=""):
        def esc(s):
            return s.replace("'", "'\\''")

        remote_cmd = "bash '{script}' '{a}' '{u}' '{p}' '{pm}' '{lf}'".format(
            script=SCRIPT,
            a=esc(action), u=esc(user), p=esc(path), pm=esc(perm), lf=esc(LOG_FILE)
        )
        cmd = ["ssh", "-o", "ConnectTimeout=10", "-o", "BatchMode=yes", SERVER, remote_cmd]

        self._log("[{}] >>> {}\n".format(datetime.now().strftime("%H:%M:%S"), action))
        try:
            r = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                               universal_newlines=True, timeout=30)
            if r.stdout.strip():
                self._log(r.stdout)
            if r.stderr.strip():
                self._log("[STDERR] " + r.stderr)
            if not r.stdout.strip() and not r.stderr.strip():
                self._log("(sin respuesta del servidor)\n")
            if r.returncode != 0:
                self._log("[Codigo de salida: {}]\n".format(r.returncode))
        except subprocess.TimeoutExpired:
            self._log("timeout de conexion (30s)\n")
        except FileNotFoundError:
            self._log("'ssh' no encontrado en este equipo\n")
        except Exception as e:
            self._log(" {}\n".format(e))

    def _log(self, texto):
        self.salida.insert(tk.END, texto)
        self.salida.see(tk.END)
        self.root.update_idletasks()

    #Ver logs
    def ver_log(self):
        cmd = ["ssh", "-o", "ConnectTimeout=10", "-o", "BatchMode=yes", SERVER,
               "cat '{}' 2>/dev/null || echo 'El fichero de log no existe todavia'".format(LOG_FILE)]
        try:
            r = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                               universal_newlines=True, timeout=15)
            win = tk.Toplevel(self.root)
            win.title("Log FTP Admin")
            win.geometry("950x550")
            win.columnconfigure(0, weight=1)
            win.rowconfigure(1, weight=1)

            ttk.Label(win, text="Log: " + LOG_FILE, font=("Arial", 10, "bold")).grid(
                row=0, column=0, sticky="w", padx=10, pady=(8, 4))

            txt = scrolledtext.ScrolledText(win, font=("Consolas", 9))
            txt.grid(row=1, column=0, sticky="nsew", padx=10, pady=(0, 4))

            contenido = r.stdout or r.stderr or "(vacio)"
            txt.insert(tk.END, contenido)
            txt.see(tk.END)
            txt.config(state="disabled")

            ttk.Button(win, text="Cerrar", command=win.destroy).grid(row=2, column=0, pady=8)
        except subprocess.TimeoutExpired:
            self._log("timeout leyendo el log\n")
        except Exception as e:
            self._log("leyendo log: {}\n".format(e))

    def crear_usuario(self):
        u = self.entry_user.get().strip()
        if not u:
            messagebox.showwarning("Campo vacio", "Introduce un nombre de usuario")
            return
        self._ssh("creador_usuarios", user=u)

    def crear_usuario_carpeta(self):
        u = self.entry_user.get().strip()
        c = self.entry_path.get().strip()
        if not u:
            messagebox.showwarning("Campo vacio", "Introduce un nombre de usuario")
            return
        if not c:
            c = u
        ruta_final = c if c.startswith("/") else "/home/{}/{}".format(u, c)
        self._log("carpeta destino -> {}\n".format(ruta_final))
        self._ssh("creador_usuarios_carpeta", user=u, path=ruta_final)

    def borrar_usuario(self):
        u = self.entry_user.get().strip()
        if not u:
            messagebox.showwarning("Campo vacio", "Introduce un nombre de usuario")
            return
        if not messagebox.askyesno("Confirmar", "Borrar usuario '{}' y su home?\nEsta accion no se puede deshacer.".format(u)):
            return
        self._ssh("borrar_usuarios", user=u)

    def crear_carpeta(self):
        p = self.entry_path.get().strip()
        if not p:
            messagebox.showwarning("Campo vacio", "Introduce una ruta")
            return
        self._ssh("crear_carpetas", path=p)

    def borrar_carpeta(self):
        p = self.entry_path.get().strip()
        if not p:
            messagebox.showwarning("Campo vacio", "Introduce una ruta")
            return
        if not messagebox.askyesno("Confirmar", "Borrar la carpeta '{}' y todo su contenido?".format(p)):
            return
        self._ssh("borrar_carpetas", path=p)

    def permisos(self):
        p  = self.entry_path.get().strip()
        pm = self.entry_perm.get().strip()
        if not p or not pm:
            messagebox.showwarning("Campos vacios", "Introduce ruta y permisos")
            return
        self._ssh("dar_permisos", path=p, perm=pm)

    def propietario(self):
        u = self.entry_user.get().strip()
        p = self.entry_path.get().strip()
        if not u or not p:
            messagebox.showwarning("Campos vacios", "Introduce usuario y ruta")
            return
        self._ssh("cambiar_propietario", user=u, path=p)

    def listar(self):
        p = self.entry_path.get().strip()
        if not p:
            messagebox.showwarning("Campo vacio", "Introduce una ruta")
            return
        if not p.startswith("/"):
            u = self.entry_user.get().strip() or "usuario"
            messagebox.showwarning(
                "Ruta relativa",
                "Usa una ruta absoluta.\nEjemplo: /home/{}/{}".format(u, p)
            )
            return
        self._ssh("listar", path=p)


#Abre y ejecuta el entorno grafico
if __name__ == "__main__":
    root = tk.Tk()
    FTPAdmin(root)
    root.mainloop()