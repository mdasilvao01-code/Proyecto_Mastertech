Write-Host "Instalando IIS y FTP Server..."
Install-WindowsFeature -Name Web-Server, Web-Ftp-Server, Web-Mgmt-Console

New-Item -Path "C:\FTP" -ItemType Directory
Write-Host "Configuración completada."