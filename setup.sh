#!/bin/bash

# 1. Actualizar el sistema e instalar dependencias (Amazon Linux 2023)
echo "--- Instalando Apache, PHP y Driver MySQL ---"
sudo dnf update -y
sudo dnf install -y httpd php php-mysqli php-fpm

# 2. Iniciar y habilitar el servidor web
echo "--- Iniciando servicios ---"
sudo systemctl start httpd
sudo systemctl enable httpd

# 3. Configurar permisos de la carpeta web
# Añadimos el usuario ec2-user al grupo apache y damos permisos de escritura
echo "--- Configurando permisos de usuario ---"
sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec sudo chmod 2775 {} \;
find /var/www -type f -exec sudo chmod 0664 {} \;

# 4. Configurar SELinux para permitir que Apache conecte a la base de datos RDS
echo "--- Configurando SELinux para permitir conexión a DB ---"
sudo setsebool -P httpd_can_network_connect_db 1

# 5. Crear archivo .gitignore
echo "--- Creando .gitignore ---"
cat <<EOF > /var/www/html/.gitignore
.env
EOF

# 6. Crear archivo .env de ejemplo (deberás editarlo con tus datos reales)
echo "--- Creando .env de ejemplo ---"
cat <<EOF > /var/www/html/.env
DB_HOST=tu-endpoint-rds.amazonaws.com
DB_USER=admin
DB_PASS=TuPasswordSeguro
DB_NAME=hola_db
EOF

echo "--------------------------------------------------"
echo "¡Instalación completada!"
echo "IMPORTANTE: Edita /var/www/html/.env con tus credenciales reales."
echo "Luego sube tus archivos index.php y styles.css a /var/www/html/"
echo "--------------------------------------------------"
