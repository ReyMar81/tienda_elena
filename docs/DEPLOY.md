# 🚀 Guía de Despliegue - Tienda Elena

Esta guía cubre el despliegue de la aplicación en servidores Linux y Windows con Nginx o Apache.

---

## Despliegue en Linux (Ubuntu/Debian)

### 1. Requisitos del Servidor

```bash
# Actualizar paquetes
sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.1+ y extensiones
sudo apt install -y php8.1 php8.1-fpm php8.1-cli php8.1-common php8.1-mbstring \
    php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-pgsql php8.1-bcmath

# Instalar PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Instalar Nginx
sudo apt install -y nginx

# Instalar Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Configurar PostgreSQL

```bash
# Acceder a PostgreSQL
sudo -u postgres psql

# Crear base de datos y usuario
CREATE DATABASE tienda_elena;
CREATE USER tienda_user WITH ENCRYPTED PASSWORD 'contraseña_segura';
GRANT ALL PRIVILEGES ON DATABASE tienda_elena TO tienda_user;
\q
```

### 3. Clonar y Configurar Aplicación

```bash
# Crear directorio web
sudo mkdir -p /var/www/tienda-elena
sudo chown -R $USER:$USER /var/www/tienda-elena

# Clonar repositorio
cd /var/www/tienda-elena
git clone https://github.com/tu-usuario/tienda-elena.git .

# Instalar dependencias
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Configurar .env
cp .env.example .env
nano .env
```

Configurar `.env` para producción:

```env
APP_NAME="Tienda Elena"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tienda_elena
DB_USERNAME=tienda_user
DB_PASSWORD=contraseña_segura
```

```bash
# Generar clave y ejecutar migraciones
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

### 4. Configurar Permisos

```bash
# Cambiar propietario a www-data (usuario de Nginx/PHP-FPM)
sudo chown -R www-data:www-data /var/www/tienda-elena

# Permisos de escritura para storage y cache
sudo chmod -R 775 /var/www/tienda-elena/storage
sudo chmod -R 775 /var/www/tienda-elena/bootstrap/cache

# Agregar usuario actual al grupo www-data
sudo usermod -a -G www-data $USER
```

### 5. Configurar Nginx

Crear archivo de configuración:

```bash
sudo nano /etc/nginx/sites-available/tienda-elena
```

Contenido:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tu-dominio.com www.tu-dominio.com;
    root /var/www/tienda-elena/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
```

Activar sitio:

```bash
sudo ln -s /etc/nginx/sites-available/tienda-elena /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Configurar SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Renovación automática (ya configurada por Certbot)
sudo certbot renew --dry-run
```

### 7. Optimizar para Producción

```bash
cd /var/www/tienda-elena

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Habilitar OPcache (editar php.ini)
sudo nano /etc/php/8.1/fpm/php.ini
```

Configurar OPcache:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.validate_timestamps=0
```

Reiniciar PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

---

## Despliegue en Windows Server

### 1. Requisitos

-   Windows Server 2016 o superior
-   IIS 10+
-   PHP 8.1+ instalado con extensiones (pdo_pgsql, mbstring, etc.)
-   PostgreSQL 13+
-   URL Rewrite Module para IIS
-   Git for Windows

### 2. Configurar IIS

1. Instalar IIS con CGI desde "Server Manager"
2. Instalar "URL Rewrite Module" desde [IIS.net](https://www.iis.net/downloads/microsoft/url-rewrite)
3. Configurar PHP Handler en IIS

### 3. Configurar Sitio Web

1. Clonar repositorio en `C:\inetpub\wwwroot\tienda-elena`
2. Ejecutar desde PowerShell:

```powershell
cd C:\inetpub\wwwroot\tienda-elena
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Configurar .env
Copy-Item .env.example .env
notepad .env

# Ejecutar artisan
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

3. Crear nuevo sitio en IIS Manager:

    - Physical path: `C:\inetpub\wwwroot\tienda-elena\public`
    - Application pool: PHP (sin modo pipeline integrado)

4. Configurar permisos de escritura:
    - Clic derecho en carpetas `storage` y `bootstrap/cache`
    - Properties → Security → Edit
    - Agregar permisos completos para `IIS_IUSRS`

### 4. Configurar web.config

Crear `public/web.config`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)/$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```

---

## Backup Automático Semanal

### Linux (Cron)

```bash
# Editar crontab
crontab -e

# Agregar backup semanal (cada domingo a las 2 AM)
0 2 * * 0 cd /var/www/tienda-elena && /usr/bin/php artisan backup:db >> /var/log/tienda-backup.log 2>&1

# Backup diario adicional (opcional)
0 3 * * * cd /var/www/tienda-elena && /usr/bin/php artisan backup:db >> /var/log/tienda-backup.log 2>&1
```

### Windows (Task Scheduler)

1. Abrir "Task Scheduler"
2. Crear nueva tarea básica
3. Trigger: Semanal, domingo 2:00 AM
4. Acción: Iniciar programa
    - Programa: `C:\php\php.exe`
    - Argumentos: `artisan backup:db`
    - Carpeta: `C:\inetpub\wwwroot\tienda-elena`

### Script Bash para Rotación de Backups (Linux)

Crear `/var/www/tienda-elena/scripts/backup-rotate.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/www/tienda-elena/storage/app/backups"
DAYS_TO_KEEP=30

# Ejecutar backup
cd /var/www/tienda-elena
php artisan backup:db

# Eliminar backups antiguos
find $BACKUP_DIR -name "backup_*.sql" -type f -mtime +$DAYS_TO_KEEP -delete

echo "Backup completado y antiguos eliminados (>$DAYS_TO_KEEP días)"
```

Dar permisos:

```bash
chmod +x /var/www/tienda-elena/scripts/backup-rotate.sh
```

Programar en crontab:

```bash
0 2 * * 0 /var/www/tienda-elena/scripts/backup-rotate.sh >> /var/log/tienda-backup.log 2>&1
```

---

## Configurar Supervisor para Queues (Opcional)

Si usas colas de Laravel:

```bash
# Instalar Supervisor
sudo apt install -y supervisor

# Crear configuración
sudo nano /etc/supervisor/conf.d/tienda-elena-worker.conf
```

Contenido:

```ini
[program:tienda-elena-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/tienda-elena/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/tienda-elena/storage/logs/worker.log
stopwaitsecs=3600
```

Iniciar Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tienda-elena-worker:*
```

---

## Monitoreo y Mantenimiento

### Logs Importantes

-   Laravel: `storage/logs/laravel.log`
-   Nginx: `/var/log/nginx/error.log`
-   PHP-FPM: `/var/log/php8.1-fpm.log`

### Comandos de Mantenimiento

```bash
# Limpiar logs antiguos
php artisan log:clear

# Limpiar caché completo
php artisan optimize:clear

# Monitorear uso de disco
df -h

# Ver procesos PHP
ps aux | grep php
```

---

## Checklist de Despliegue

-   [ ] PostgreSQL configurado y funcionando
-   [ ] PHP 8.1+ instalado con todas las extensiones
-   [ ] Nginx/Apache configurado correctamente
-   [ ] SSL certificado instalado
-   [ ] `.env` configurado para producción (APP_DEBUG=false)
-   [ ] Migraciones ejecutadas
-   [ ] Seeders ejecutados
-   [ ] `storage:link` creado
-   [ ] Permisos de storage y cache configurados
-   [ ] Assets compilados (npm run build)
-   [ ] Cachés generados (config, route, view)
-   [ ] Backup automático programado
-   [ ] Supervisor configurado (si usa queues)
-   [ ] Logs de errores monitoreados

---

**¿Necesitas ayuda?** Consulta [INSTALL.md](INSTALL.md) para instalación local.
