# 📦 Guía de Instalación - Tienda Elena

## Requisitos del Sistema

-   **PHP** ≥ 8.1 (con extensiones: pdo_pgsql, mbstring, xml, curl, zip, bcmath, gd)
-   **PostgreSQL** ≥ 13
-   **Composer** ≥ 2.5
-   **Node.js** ≥ 18.x
-   **npm** ≥ 9.x

---

## Instalación Paso a Paso

### 1. Clonar Repositorio

```bash
git clone https://github.com/tu-usuario/tienda-elena.git
cd tienda-elena
```

### 2. Instalar Dependencias de PHP

```bash
composer install
```

### 3. Configurar Variables de Entorno

Copiar archivo de configuración:

```bash
cp .env.example .env
```

Editar `.env` con los datos de tu base de datos PostgreSQL:

```env
APP_NAME="Tienda Elena"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tienda_elena
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@tiendaelena.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 5. Crear Base de Datos

Crear base de datos en PostgreSQL:

```sql
CREATE DATABASE tienda_elena;
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

### 7. Ejecutar Seeders

```bash
php artisan db:seed
```

Esto creará:

-   Usuario administrador: `admin@tiendaelena.com` / `admin123`
-   Roles y permisos iniciales
-   Categorías y productos de ejemplo
-   Configuraciones del sistema

### 8. Crear Enlace Simbólico de Storage

```bash
php artisan storage:link
```

### 9. Instalar Dependencias de Node.js

```bash
npm install
```

### 10. Compilar Assets para Desarrollo

```bash
npm run dev
```

O para producción:

```bash
npm run build
```

### 11. Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

---

## Comandos Importantes

### Optimización de Producción

```bash
# Optimizar autoload de Composer
composer install --optimize-autoloader --no-dev

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar aplicación completa
php artisan optimize
```

### Limpiar Cachés

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

### Mantenimiento de Base de Datos

```bash
# Revertir migraciones
php artisan migrate:rollback

# Refrescar base de datos (elimina todo)
php artisan migrate:fresh

# Refrescar con seeders
php artisan migrate:fresh --seed

# Backup manual de base de datos
php artisan backup:db
```

### Assets Frontend

```bash
# Desarrollo con hot reload
npm run dev

# Compilación de producción
npm run build

# Limpiar caché de Vite
rm -rf node_modules/.vite
```

---

## Verificación de Instalación

1. Acceder a `http://localhost:8000`
2. Iniciar sesión con: `admin@tiendaelena.com` / `admin123`
3. Verificar que el dashboard cargue correctamente
4. Probar navegación del menú lateral
5. Verificar que los gráficos se rendericen
6. Probar creación de un producto de prueba
7. Verificar que las imágenes se suban correctamente

---

## Solución de Problemas Comunes

### Error: "Class not found"

```bash
composer dump-autoload
php artisan optimize:clear
```

### Error: "Storage link already exists"

```bash
rm public/storage
php artisan storage:link
```

### Error: "SQLSTATE[08006] Connection refused"

-   Verificar que PostgreSQL esté corriendo
-   Revisar credenciales en `.env`
-   Verificar puerto (5432 por defecto)

### Error: "npm run dev" falla

```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Permisos de escritura en Linux

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Próximos Pasos

-   Consultar [DEPLOY.md](DEPLOY.md) para instrucciones de despliegue en producción
-   Revisar configuración de correo electrónico
-   Personalizar temas y colores en `resources/js/Layouts/AppLayout.vue`
-   Agregar productos y categorías reales
-   Configurar backup automático

---

**Desarrollado con ❤️ usando Laravel 11 + Inertia.js + Vue 3**
