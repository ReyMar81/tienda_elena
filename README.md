# 🛍️ Tienda Elena - E-COMMERCE CON SISTEMA DE CRÉDITOS Y CUOTAS

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
<img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
<img src="https://img.shields.io/badge/Inertia.js-1.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
<img src="https://img.shields.io/badge/PostgreSQL-13+-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
<img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
</p>

---

## 📋 INFORMACIÓN DEL PROYECTO

**Materia:** INF-513 TECNOLOGÍA WEB  
**Proyecto:** Proyecto 2 - Sistema Web con Base de Datos  
**Grupo:** 22sa  
**Fecha:** 2025-2  
**Archivo Entrega:** `2025-2_INF513-P2_grupo22sa.tar.gz`

---

## 🎯 DESCRIPCIÓN GENERAL

**Tienda Elena** es un sistema de E-commerce para venta de ropa con funcionalidades avanzadas de gestión de créditos, cuotas, intereses y moras. Implementa arquitectura de tres capas con roles diferenciados y control completo del negocio.

Sistema completo de gestión de ventas con catálogo de productos, carrito de compras, procesamiento de ventas al contado y a crédito, generación de boletas en PDF, control de inventario con kardex, reportes estadísticos y sistema de gestión de pagos.

**Arquitectura:** MVC + Servicios (Business Layer) + PostgreSQL implementando arquitectura de 3 capas:

-   **Capa de Presentación:** Vue 3 + Inertia.js + Bootstrap 5 (100% sin Tailwind)
-   **Capa de Lógica de Negocio:** Controllers + Services (CreditService, ReportService, PaymentService, etc.)
-   **Capa de Persistencia:** Models Eloquent + PostgreSQL

### Actores del Sistema

-   **Propietario**: Administrador del negocio (máximo nivel de permisos)
-   **Vendedor**: Gestiona ventas, pedidos y productos
-   **Cliente**: Realiza compras, gestiona su perfil y visualiza sus créditos

---

## ✨ Características Principales

-   🎨 **3 Temas con Modo Claro/Oscuro**: Azul Clásico (Niños), Esmeralda Moderno (Jóvenes), Púrpura Elegante (Adultos)
-   🔐 **Control de Acceso Basado en Roles (RBAC)**: Propietario, Vendedor, Cliente
-   📊 **Dashboard Interactivo**: Gráficos con Chart.js (ventas, créditos, productos)
-   🛍️ **Catálogo de Productos**: Filtros por categoría, búsqueda, promociones automáticas
-   🛒 **Carrito de Compras**: Dual mode (localStorage para invitados + BD para autenticados)
-   💳 **Ventas al Contado y a Crédito**: Procesamiento completo con reducción automática de stock
-   📄 **Generación de Boletas**: PDF A4 y Ticket térmico 80mm con código QR
-   📦 **Control de Inventario**: Kardex automático de entradas/salidas
-   💰 **Gestión de Créditos y Pagos**: Calendario de cuotas, registro de pagos, cálculo de intereses y moras
-   📈 **Reportes Estadísticos**: 6 tipos con exportación a PDF (ventas, créditos, productos, clientes, inventario)
-   👁️ **Contador de Visitas**: Tracking inteligente de páginas visitadas por URL
-   🔍 **Búsqueda Global**: Campo en encabezado con resultados en tiempo real
-   🧭 **Menú Dinámico**: Cargado desde PostgreSQL y filtrado por rol de usuario
-   ♿ **Accesibilidad**: ARIA labels, navegación por teclado, alto contraste, tamaños de fuente escalables

---

## 📋 Documentación

-   **[📦 Guía de Instalación](docs/INSTALL.md)** - Instrucciones paso a paso para instalación local
-   **[🚀 Guía de Despliegue](docs/DEPLOY.md)** - Configuración para servidores Linux/Windows

---

## 🔑 Credenciales Iniciales

Después de ejecutar las migraciones y seeders:

| Rol         | Email                 | Contraseña |
| ----------- | --------------------- | ---------- |
| Propietario | admin@tiendaelena.com | admin123   |

> ⚠️ **Importante**: Cambiar estas contraseñas en producción.
>
> **Nota**: El seeder actual solo crea el usuario Propietario. Para crear usuarios adicionales (Vendedor, Cliente), puede hacerlo desde el panel de administración en Gestión de Usuarios.

---

## ⚡ Inicio Rápido

```bash
# Clonar repositorio
git clone https://github.com/tu-usuario/tienda-elena.git
cd tienda-elena

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos PostgreSQL en .env
# DB_CONNECTION=pgsql
# DB_DATABASE=tienda_elena
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Crear enlace simbólico para storage
php artisan storage:link

# Compilar assets y iniciar servidor
npm run dev
php artisan serve
```

Acceder a: `http://localhost:8000`

---

## 🏗️ ARQUITECTURA TÉCNICA

### Evolución desde Proyectos Anteriores

#### Aprendizaje Aplicado de la Materia INF-513

Este proyecto representa la evolución natural de las técnicas aprendidas en clase durante proyectos previos:

| Concepto                 | Implementación en Clase                    | Implementación Actual          | Justificación                                                     |
| ------------------------ | ------------------------------------------ | ------------------------------ | ----------------------------------------------------------------- |
| **Capa de Presentación** | Smarty Templates + HTML                    | Inertia.js + Vue 3             | Mantiene separación vista-lógica, ahora con componentes reactivos |
| **Capa de Negocio**      | Gestores PHP (ej. `gestorAdmPer.php`)      | Laravel Services + Controllers | Misma filosofía de encapsular lógica, con mejor organización      |
| **Capa de Datos**        | ADODB + PostgreSQL                         | Eloquent ORM + PostgreSQL      | Misma BD, mejor abstracción y seguridad                           |
| **Roles y Permisos**     | `$_SESSION['nivel']` en PHP                | Policies + Middleware + Gates  | Implementación robusta del concepto ya trabajado                  |
| **Menús Dinámicos**      | Smarty + Arrays PHP                        | BD + Inertia Props Globales    | Evolución del menú adaptativo por rol                             |
| **Validaciones**         | `htmlspecialchars` + validaciones manuales | Form Requests + Reglas Laravel | Validación dual más robusta                                       |

**🎯 Principio fundamental mantenido:** Arquitectura de tres capas con separación estricta de responsabilidades.

#### Laravel Jetstream: Base de Autenticación Profesional

**¿Por qué Jetstream?**

En los proyectos anteriores implementamos manualmente:

-   Sistema de login/logout
-   Recuperación de contraseña
-   Gestión de sesiones
-   Control de acceso básico

**Laravel Jetstream** no es una plantilla de e-commerce, sino un **starter kit de autenticación nativo** que proporciona:

✅ Sistema de autenticación completo (reemplaza login manual de clase)  
✅ Recuperación de contraseña con tokens seguros  
✅ Gestión de sesiones y "remember me"  
✅ **Integración nativa con Inertia.js + Vue 3** (stack pre-configurado)  
✅ Two-factor authentication (2FA) opcional  
✅ Laravel Sanctum para API tokens  
✅ Perfil de usuario editable con componentes Vue listos  
✅ Gestión de equipos (teams) opcional  
✅ **Vistas adaptadas a Bootstrap 5** estilizando componentes de autenticación

**Ventaja:** Nos permite enfocarnos en la lógica del e-commerce (créditos, cuotas, moras) en lugar de reinventar autenticación básica. **Las vistas de Jetstream se adaptan a Bootstrap** sin reescribir funcionalidades.

### Stack Tecnológico Obligatorio

| Capa                      | Tecnología             | Versión               |
| ------------------------- | ---------------------- | --------------------- |
| **Backend**               | Laravel                | 11.x                  |
| **Starter Kit**           | Laravel Jetstream      | Latest (Inertia)      |
| **Frontend Framework**    | Vue.js                 | 3.x (Composition API) |
| **Integración**           | Inertia.js             | Latest                |
| **CSS Framework**         | Bootstrap              | 5.x                   |
| **Base de Datos**         | PostgreSQL             | 14+                   |
| **Patrón Arquitectónico** | MVC/MVVM               | -                     |
| **ORM**                   | Eloquent               | Laravel 11            |
| **Autenticación**         | Jetstream (Nativa)     | bcrypt + Sanctum      |
| **Lógica de Negocio**     | Services + Controllers | -                     |
| **Build Tool**            | Vite                   | Latest                |

### Arquitectura de Tres Capas

```
┌─────────────────────────────────────────────────────────┐
│   CAPA DE PRESENTACIÓN                                  │
│   (Inertia.js + Vue 3 Composition API)                  │
│   - Componentes Vue SFC (reemplazan Smarty de clase)    │
│   - Bootstrap 5 para estilos (framework de clase)       │
│   - UI/UX Responsivo                                    │
│   - Temas dinámicos con CSS personalizado               │
│   - Blade mínimo (solo app.blade.php para Inertia)      │
└─────────────────────────────────────────────────────────┘
                           ↕
┌─────────────────────────────────────────────────────────┐
│   CAPA DE NEGOCIO                                       │
│   (Laravel 11 + Services Pattern)                       │
│   - Controllers (orquestan peticiones)                  │
│   - Services (lógica de negocio encapsulada)            │
│     * Reemplazan gestores de clase (gestorAdmPer, etc.) │
│     * Ej: CreditService, OrderService, ProductService   │
│   - Policies/Middleware (permisos por rol)              │
│   - Form Requests (validaciones)                        │
└─────────────────────────────────────────────────────────┘
                           ↕
┌─────────────────────────────────────────────────────────┐
│   CAPA DE DATOS                                         │
│   (PostgreSQL + Eloquent ORM)                           │
│   - Models (reemplazan ADODB de clase)                  │
│   - Migrations (estructura BD)                          │
│   - Seeders (datos iniciales)                           │
│   - Relationships (relaciones entre modelos)            │
└─────────────────────────────────────────────────────────┘
```

### 🏛️ Arquitectura de 3 Capas (Cumplimiento Total)

#### Capa 1: Presentación (Frontend)

-   **Tecnologías:** Vue 3 Composition API + Inertia.js + Bootstrap 5.3.8
-   **Componentes:** 45+ archivos `.vue` en `resources/js/Pages/` (Auth, Cart, Catalog, **Categorias**, Credits, Dashboard, **Productos**, Profile, **Promociones**, Reportes, Ventas, Welcome)
-   **Layouts:** `AppLayout.vue` (navbar, sidebar, footer, themes)
-   **Componentes Comunes:** `FlashNotification.vue` (Toast), `Dropdown.vue`, `Modal.vue`, `InputError.vue`, etc.
-   **Composables:** `useCart.js`, `useDashboard.js`, `useSearch.js`, `useTheme.js`
-   **Build:** Vite con hot reload

#### Capa 2: Lógica de Negocio (Backend)

-   **Controllers:** 16 controllers en `app/Http/Controllers/` (Cart, Catalog, Categoria, Credito, Dashboard, Invoice, Menu, MetodoPago, Pago, Producto, Promocion, Report, Search, User, Venta)
-   **Policies:** 4 policies en `app/Policies/` (ProductoPolicy, CategoriaPolicy, PromocionPolicy, UserPolicy) - Autorización dinámica consultando menu_items por rol
-   **Services:** 9 services en `app/Services/` (separación de responsabilidades)
-   **Middleware:** `TrackPageVisits`, `RoleMiddleware`, `HandleInertiaRequests`
-   **Validation:** 11 Form Requests en `app/Http/Requests/` (Store/Update para Producto, Categoria, Promocion, User + CrearCliente, CrearCredito, RegistrarPago)
-   **Commands:** `BackupDatabase` (Artisan personalizado)

#### Capa 3: Persistencia (Base de Datos)

-   **ORM:** Eloquent (Laravel)
-   **BD:** PostgreSQL 13+
-   **Models:** 18 modelos (User, Role, Producto, Venta, Credito, Pago, Carrito, MenuItem, etc.)
-   **Migrations:** 28 migraciones
-   **Seeders:** 6 seeders (Role, User, MenuItem, MetodosPago, Categoria, Producto)

---

## 📦 CASOS DE USO IMPLEMENTADOS

Todos los casos de uso del enunciado del proyecto están implementados y funcionales:

### CU1. Gestión de Usuarios

-   ✅ Registro de usuarios con validación (Jetstream)
-   ✅ Login y logout seguro (Jetstream + bcrypt)
-   ✅ Recuperación de contraseña con tokens temporales
-   ✅ Asignación y gestión de roles (Propietario/Vendedor/Cliente)
-   ✅ Gestión de perfil de usuario (Jetstream Profile)
-   ✅ Control de acceso basado en roles (Middleware + Policies)
-   ✅ **CRUD completo de usuarios (UserController + UserPolicy + StoreUserRequest/UpdateUserRequest)**
-   ✅ **Búsqueda de usuarios por nombre/email/CI, paginación (15/página)**
-   ✅ **Validación de CI y email únicos, password confirmado (min 8 caracteres)**
-   ✅ **Hash de contraseñas con bcrypt, sincronización de roles (many-to-many)**
-   ✅ **Policy evita auto-eliminación de usuarios**
-   **Modelos:** `User`, `Role`, tabla pivote `role_user`
-   **Seeders:** `RoleSeeder`, `UserSeeder`
-   **Controller:** `UserController` (CRUD completo con autorización Policy)
-   **Policy:** `UserPolicy` (verifica permisos desde menu_items, evita $user->id === $model->id en delete)
-   **Rutas:** `/users/*` (protegidas por middleware role:propietario + Policy)
-   **Funcionalidad:** RBAC completo con Jetstream como base de autenticación, gestión administrativa de usuarios por Propietario

### CU2. Gestión de Productos

-   ✅ CRUD completo de productos (ProductoController + ProductoPolicy + StoreProductoRequest/UpdateProductoRequest)
-   ✅ Categorías jerárquicas (CategoriaController + CategoriaPolicy + StoreCategoriaRequest/UpdateCategoriaRequest)
-   ✅ Control de stock y kardex automático
-   ✅ Promociones con descuentos automáticos (PromocionController + PromocionPolicy + StorePromocionRequest/UpdatePromocionRequest)
-   ✅ Gestión de atributos: nombre, categoría, código, precio, stock, imagen con upload a storage
-   ✅ Vistas Vue completas: Index (tabla paginada con búsqueda), Create (formulario con preview), Edit (edición), Show (detalle)
-   ✅ Lazy loading de imágenes en listados, Eager loading de categorías (evita N+1)
-   ✅ Validaciones frontend (Vue) + backend (Form Requests) con mensajes en español
-   ✅ Permisos dinámicos desde BD: Propietario (CRUD completo), Vendedor (solo lectura productos), Cliente (sin acceso gestión)
-   **Controllers:** `ProductoController`, `CategoriaController`, `PromocionController`
-   **Policies:** `ProductoPolicy`, `CategoriaPolicy`, `PromocionPolicy` (consultan menu_items por rol)
-   **Services:** `ProductService`, `PromotionService`
-   **Models:** `Producto`, `Categoria`, `Promocion`, `KardexInventario`
-   **Vistas Vue:** `Productos/{Index,Create,Edit,Show}.vue`, `Categorias/{Index,Create,Edit,Show}.vue`, `Promociones/{Index,Create,Edit,Show}.vue`
-   **Componente:** `FlashNotification.vue` (notificaciones Toast con auto-hide 5seg)
-   **Rutas:** `/productos/*`, `/categorias/*`, `/promociones/*` (protegidas por middleware role + Policy)

### CU3. Gestión de Pedidos

-   ✅ Carrito de compras interactivo (localStorage + BD)
-   ✅ Creación de pedidos desde carrito
-   ✅ Detalle de líneas de pedido (productos con cantidad y precio)
-   ✅ Historial de compras por usuario
-   ✅ Gestión de pedidos por vendedor (VentaController)
-   **Pantallas:** `Pages/Cart/Index.vue`, `Pages/Catalog/Index.vue`, `Pages/Catalog/Show.vue`
-   **Controllers:** `CartController`, `CatalogController`, `VentaController`
-   **Services:** `OrderService`, `ProductService`
-   **Models:** `Carrito`, `CarritoDetalle`, `Venta`, `VentaDetalle`
-   **Funcionalidad:** Sistema dual (localStorage invitados + BD autenticados), sincronización automática al login

### CU4. Gestión de Ventas

-   ✅ Registro de ventas al contado (pago completo inmediato)
-   ✅ Registro de ventas a crédito (plan de cuotas automático con intereses)
-   ✅ Relación venta-pedido con detalles completos
-   ✅ Registro de vendedor responsable
-   ✅ Reducción automática de stock (KardexInventario)
-   ✅ Generación de boletas PDF A4 y Ticket 80mm con QR
-   **Pantallas:** `Pages/Cart/Index.vue` (checkout), `Pages/Ventas/Show.vue` (boleta)
-   **Controllers:** `VentaController`, `InvoiceController`
-   **Services:** `OrderService`, `CreditService`
-   **Models:** `Venta`, `VentaDetalle`, `KardexInventario`
-   **Rutas:** `/ventas/contado`, `/ventas/credito`, `/ventas/{id}/boleta`, `/ventas/{id}/pdf`, `/ventas/{id}/ticket`

### CU5. Gestión de Créditos y Moras

-   ✅ Definición de planes de crédito (cuotas, interés, frecuencia)
-   ✅ Generación automática de cuotas con fechas de vencimiento
-   ✅ Registro de pagos parciales de cuotas (PagoController)
-   ✅ Cálculo automático de intereses sobre saldo pendiente
-   ✅ Cálculo y registro de moras por retraso en pagos
-   ✅ Estados del crédito: Pendiente, Al día, Vencido, Liquidado
-   ✅ Historial completo de pagos por crédito
-   **Pantallas:** `Pages/Credits/Index.vue`, `Pages/Credits/Show.vue`, `Pages/Credits/MisCreditos.vue` (clientes)
-   **Controllers:** `CreditoController`, `PagoController`
-   **Services:** `CreditService`, `PaymentService`
-   **Models:** `Credito`, `Cuota`, `Pago`
-   **Rutas:** `/creditos/*` (Propietario/Vendedor), `/mis-creditos` (Cliente)

### CU6. Gestión de Promociones

-   ✅ Creación de promociones por producto (PromocionController + PromocionPolicy)
-   ✅ Promociones por categoría
-   ✅ Promociones por monto mínimo de compra
-   ✅ Promociones por rango de fechas (fecha_inicio, fecha_fin)
-   ✅ Descuento porcentual o precio fijo promocional
-   ✅ Activación/desactivación de promociones
-   ✅ Aplicación automática en catálogo y carrito
-   ✅ **Validación de fechas: fecha_fin > fecha_inicio, descuento 0-100%**
-   ✅ **Relaciones many-to-many con productos y categorías (attach/sync/detach)**
-   ✅ **Vistas Vue con selección múltiple (Ctrl+Click), estado activa/inactiva calculado dinámicamente**
-   **Controllers:** `PromocionController` (CRUD completo con autorización Policy)
-   **Services:** `PromotionService`
-   **Models:** `Promocion` (tablas pivot: promocion_productos, promocion_categorias)
-   **Policy:** `PromocionPolicy` (verifica permisos desde menu_items por rol)
-   **Vistas Vue:** `Promociones/{Index,Create,Edit,Show}.vue` con badge de estado y formateo de fechas
-   **Rutas:** `/promociones/*` (Propietario)

### CU7. Gestión de Pagos

-   ✅ Catálogo de métodos de pago (efectivo, tarjeta, transferencia)
-   ✅ Registro de métodos de pago por usuario
-   ✅ Registro de pagos únicos (ventas al contado)
-   ✅ Registro de pagos de cuotas (ventas a crédito)
-   ✅ **Arquitectura preparada para integración de pasarela** (PasarelaPagoService)
-   ✅ Generación de QR simulado (UUID) para pagos electrónicos
-   ✅ Puntos de extensión claramente identificados para PagoFácil/Tigo Money
-   **Pantallas:** `Pages/Pagos/Index.vue`, `Pages/Pagos/Create.vue`
-   **Controllers:** `PagoController`, `MetodoPagoController`
-   **Services:** `PaymentService`, `PasarelaPagoService`
-   **Models:** `Pago`, `MetodoPago`
-   **Rutas:** `/pagos/*` (Propietario/Vendedor), `/pagos/generar-qr` (Cliente)

### CU8. Reportes y Estadísticas

-   ✅ Ventas por período (fecha inicio/fin) con exportación PDF
-   ✅ Ventas por vendedor (individual o comparativo)
-   ✅ Ventas por tipo (contado vs crédito) con totales
-   ✅ Créditos en mora vs créditos al día
-   ✅ Montos cobrados y pendientes por período
-   ✅ Top productos más vendidos (ranking configurable)
-   ✅ Actividad de usuarios (accesos/visitas) con PageVisitService
-   ✅ Visitas por página (Middleware TrackPageVisits)
-   ✅ Gráficos interactivos con Chart.js (Dashboard)
-   **Pantallas:** `Pages/Reportes/Index.vue`, `Pages/Reportes/Show.vue`, `Pages/Dashboard.vue`
-   **Controllers:** `ReportController`, `DashboardController`
-   **Services:** `ReportService` (6 métodos), `PageVisitService`
-   **Reportes PDF:** ventas-fecha, ventas-metodo, creditos-estado, productos-vendidos, clientes-top, inventario-critico
-   **Rutas:** `/dashboard`, `/reportes/*` (Propietario/Vendedor)

---

## ✅ CUMPLIMIENTO DE REQUISITOS MÍNIMOS DEL PROYECTO

### Requisito 1: Elementos de Diseño y Navegación

✅ **CUMPLIDO** - Bootstrap 5.3.8 como framework CSS (enseñado en clase)

-   Sidebar dinámico filtrado por rol
-   Navbar responsive con búsqueda global
-   Breadcrumbs de navegación
-   Footer con contador de visitas por página
-   Diseño consistente en todo el sitio

### Requisito 2: Dos Roles de Acceso Mínimo

✅ **CUMPLIDO** - 3 roles implementados: **Propietario, Vendedor, Cliente**

-   Tabla `users` única + tabla `roles` + tabla pivote `role_user`
-   Middleware `role:propietario`, `role:vendedor`, `role:cliente`
-   Evolución del concepto `$_SESSION['nivel']` de clase con Laravel
-   Propietario ≠ Administrador (es un rol de negocio específico)

### Requisito 3: Menú Dinámico con Base de Datos

✅ **CUMPLIDO** - Menú 100% dinámico desde PostgreSQL

-   `MenuController` + tabla `menu_items` + `MenuItemSeeder`
-   API `/api/menu` consumida por Vue (Inertia.js)
-   Filtrado automático por rol de usuario en backend
-   Protección doble: Visual (menú oculta opciones) + Funcional (middleware en rutas)
-   Soporte para menús jerárquicos (parent_id)

### Requisito 4: Arquitectura MVC-MVVM (Laravel-Inertia)

✅ **CUMPLIDO** - Laravel 11 (MVC backend) + Inertia.js 1.0 + Vue 3 (MVVM frontend)

-   **Models:** 18 modelos Eloquent con PostgreSQL
-   **Controllers:** 16 controllers (Cart, Catalog, Credito, Dashboard, Invoice, Menu, Pago, Report, Search, Venta, Producto, Categoria, Promocion, User, MetodoPago, etc.)
-   **Views:** 40+ componentes Vue en `resources/js/Pages/`
-   **Services (Business Layer):** 9 services (Credit, Order, PageVisit, PasarelaPago, Payment, Product, Promotion, Report, Dashboard)
-   Separación estricta de capas (Presentación, Negocio, Datos)

### Requisito 5: Estilo Único con 3 Temas y Accesibilidad

✅ **CUMPLIDO** - Bootstrap 5 + Temas Dinámicos

-   **3 Temas Obligatorios:**
    1. Azul Clásico (Niños) - Colores vibrantes y alegres
    2. Esmeralda Moderno (Jóvenes) - Diseño dinámico y tendencia
    3. Púrpura Elegante (Adultos) - Profesional y sobrio
-   **Modo Día/Noche:** Detección automática según hora del cliente + override manual
-   **Accesibilidad (WCAG 2.1 AA):**
    -   Contraste 4.5:1 en todos los temas
    -   Tamaños de fuente escalables (Pequeña 85%, Normal 100%, Grande 125%)
    -   Control de contraste (Normal, Alto)
    -   Navegación por teclado completa
    -   ARIA labels en todos los controles interactivos
    -   Indicadores de foco visibles
-   **Implementación:** `composables/useTheme.js`, variables CSS Bootstrap personalizadas, sin Tailwind

### Requisito 6: Validación de Entradas en Español

✅ **CUMPLIDO** - Validación dual Backend + Frontend

-   3 Form Requests en Laravel (StoreProductoRequest, UpdateProductoRequest, etc.)
-   Mensajes de error personalizados en español
-   Validación frontend con Vue (reglas personalizadas)
-   Protección automática contra XSS (Vue escapado)
-   Protección contra inyección SQL (Eloquent ORM)

### Requisito 7: Contador de Visitas por Página

✅ **CUMPLIDO** - Contador independiente por cada URL

-   Middleware `TrackPageVisits` (app/Http/Middleware/)
-   Service `PageVisitService` (app/Services/)
-   Tabla `page_visits` en PostgreSQL (url, user_id, ip, visited_at)
-   Filtrado inteligente de rutas (excluye assets, API, etc.)
-   Estadísticas mostradas en footer y reportes
-   Utilizado para análisis de tráfico individual por página

### Requisito 8: Estadísticas del Negocio

✅ **CUMPLIDO** - Dashboard Interactivo + 6 Reportes PDF

-   Dashboard con Chart.js (ventas mensuales, créditos por estado, productos más vendidos)
-   Gráficos interactivos (barras, líneas, donut)
-   6 tipos de reportes con exportación PDF:
    1. Ventas por Fecha
    2. Ventas por Método de Pago
    3. Créditos por Estado
    4. Productos Más Vendidos
    5. Clientes Top
    6. Inventario Crítico
-   Métricas en tiempo real desde PostgreSQL

### Requisito 9: Búsqueda en Encabezado

✅ **CUMPLIDO** - Búsqueda global en navbar visible en todas las páginas

-   Input de búsqueda en `AppLayout.vue` (encabezado principal)
-   `SearchController` con API `/api/search/all`
-   Resultados en tiempo real (productos, categorías, promociones)
-   Búsqueda asíncrona con Axios + Inertia
-   Vista de resultados clara y ordenada

### Requisito 10: Pagos Electrónicos

⚠️ **PARCIALMENTE IMPLEMENTADO** - Arquitectura lista para pasarela real

-   ✅ Registro de métodos de pago (tabla `metodos_pago`, seeder con 4 métodos)
-   ✅ Planes de pago (cuotas de créditos con calendario, intereses, moras)
-   ✅ Arquitectura lista para integración (`PasarelaPagoService`)
-   ✅ QR simulado generado (UUID) para pruebas de flujo
-   ✅ Interface `PaymentGatewayInterface` preparada para integrar pasarela real
-   ❌ QR real de PagoFácil/Tigo Money (falta credenciales de producción)
-   **Nota:** El sistema está preparado para activar PagoFácil con solo agregar credenciales API

---

## 🔐 SEGURIDAD Y CONTROL DE ACCESO

### Roles de Negocio (Evolución de la Arquitectura de Clase)

#### Arquitectura de Usuarios y Roles

**Como se trabajó en clase:**

En proyectos PHP previos de la materia INF-513, se utilizaban **tablas separadas por tipo de usuario**:

```
- tabla: clientes (datos de clientes)
- tabla: vendedores (datos de vendedores)
- tabla: propietarios (datos de propietarios)
```

Cada tabla tenía su propia estructura y el acceso se controlaba mediante `$_SESSION['nivel']` en cada página PHP.

**Implementación actual (modernizada con Laravel):**

En lugar de tablas separadas, se implementa el **patrón estándar de roles** con las siguientes tablas:

```
📦 users (tabla única de usuarios)
├─ id
├─ name
├─ email
├─ password (hasheada con bcrypt)
└─ timestamps

📦 roles (catálogo de roles del sistema)
├─ id
├─ name (propietario, vendedor, cliente)
├─ description
└─ timestamps

📦 role_user (tabla pivote - relación many-to-many)
├─ id
├─ user_id (FK → users)
├─ role_id (FK → roles)
└─ timestamps
```

#### Equivalencia con Conceptos de Clase

| Concepto en Clase                | Implementación Moderna                     | Cumplimiento     |
| -------------------------------- | ------------------------------------------ | ---------------- |
| `$_SESSION['nivel'] = 'cliente'` | Usuario tiene rol "cliente" en `role_user` | ✅ Misma lógica  |
| Validar nivel en cada página     | Middleware `role:cliente` en ruta          | ✅ Automatizado  |
| Tabla separada `clientes`        | Todos en `users` + filtro por rol          | ✅ Más eficiente |
| Permisos hardcodeados en PHP     | Policies dinámicas por modelo              | ✅ Más robusto   |
| `if ($_SESSION['nivel'])`        | `Route::middleware('role:propietario')`    | ✅ Modernizado   |

#### Impacto de los Roles en el Sistema

**Igual que en clase, los roles afectan:**

✅ **Visualización del menú**: Solo se muestran opciones permitidas por rol (como Smarty en clase)  
✅ **Acceso a rutas**: Middleware bloquea URLs no autorizadas (reemplaza validación manual PHP)  
✅ **Permisos de acciones**: Policies controlan create, update, delete por recurso  
✅ **Datos visibles**: Queries filtradas automáticamente por rol (ej: cliente solo ve sus pedidos)

**Ventaja sobre clase:** Los roles se gestionan desde base de datos y pueden modificarse sin cambiar código.

### Implementación de Seguridad

#### Autenticación

-   **Laravel Auth + Jetstream** con contraseñas hasheadas usando bcrypt
-   **Hash::make()** para creación de contraseñas seguras
-   **Auth::attempt()** para validación de login
-   Tokens de sesión seguros (Laravel Sanctum)
-   Recuperación de contraseña con tokens temporales
-   "Remember Me" funcional

#### Autorización

##### Middleware por Rol

```php
// Rutas protegidas por middleware de rol
Route::middleware(['auth', 'role:propietario'])->group(function () {
    Route::resource('productos', ProductoController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('promociones', PromocionController::class);
    Route::resource('usuarios', UserController::class);
});

Route::middleware(['auth', 'role:propietario,vendedor'])->group(function () {
    Route::get('/creditos', [CreditoController::class, 'index']);
    Route::post('/ventas/credito', [VentaController::class, 'storeVentaCredito']);
    Route::get('/reportes', [ReportController::class, 'index']);
});

Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/mis-creditos', [PagoController::class, 'misCreditos']);
    Route::post('/pagos/generar-qr', [PagoController::class, 'generarQR']);
});
```

##### Policies (Autorización Granular)

-   Control a nivel de modelo (view, create, update, delete)
-   Cliente solo puede ver sus propios pedidos y créditos
-   Vendedor puede gestionar ventas asignadas
-   Propietario acceso total a todos los recursos

### Principios de Seguridad

✅ **Backend filtra SIEMPRE**: Ninguna lógica de permisos en Vue (solo UI)  
✅ **Validación dual**: Backend (Form Requests) + Frontend (Vue)  
✅ **Queries filtradas por rol**: Eloquent con scopes automáticos  
✅ **CSRF Protection**: Tokens en todos los formularios (Laravel)  
✅ **SQL Injection**: Protección completa por Eloquent ORM  
✅ **XSS Protection**: Escapado automático en Vue  
✅ **Password Hashing**: bcrypt con cost factor configurable

### 🔒 Matriz de Permisos por Rol

| Funcionalidad              | Propietario | Vendedor | Cliente |
| -------------------------- | ----------- | -------- | ------- |
| Dashboard                  | ✅          | ✅       | ✅      |
| Gestión de Productos       | ✅ CRUD     | ✅ CRUD  | ❌      |
| Gestión de Categorías      | ✅ CRUD     | ❌       | ❌      |
| Gestión de Promociones     | ✅ CRUD     | ❌       | ❌      |
| Catálogo (ver)             | ✅          | ✅       | ✅      |
| Carrito de Compras         | ✅          | ✅       | ✅      |
| Crear Ventas               | ✅          | ✅       | ❌      |
| Ver Ventas                 | ✅ Todas    | ✅ Prop  | ❌      |
| Gestión de Créditos        | ✅ CRUD     | ✅ CR    | ❌      |
| Mis Créditos (cliente)     | ❌          | ❌       | ✅      |
| Gestión de Pagos           | ✅ CRUD     | ✅ CR    | ❌      |
| Generar QR Pago (cliente)  | ❌          | ❌       | ✅      |
| Reportes                   | ✅          | ✅       | ❌      |
| Gestión de Usuarios        | ✅ CRUD     | ❌       | ❌      |
| Gestión de Métodos de Pago | ✅ CRUD     | ❌       | ❌      |
| Menú Dinámico (editar)     | ✅          | ❌       | ❌      |

**Leyenda:** CRUD = Create, Read, Update, Delete | CR = Create, Read | Prop = Solo ventas propias

---

## 📊 Módulos del Sistema

### 1. Gestión de Productos

-   CRUD completo con validaciones
-   Categorías jerárquicas
-   Control de stock
-   Promociones con descuentos automáticos
-   Imágenes de producto

### 2. Catálogo y Carrito

-   Navegación pública del catálogo
-   Filtros por categoría y búsqueda
-   Carrito persistente (BD + localStorage)
-   Sincronización automática al login
-   Cálculo dinámico de descuentos

### 3. Ventas

-   **Al Contado**: Pago inmediato (efectivo, tarjeta, transferencia)
-   **A Crédito**: Generación automática de cuotas con intereses
-   Reducción automática de stock
-   Registro en kardex de inventario
-   Generación de número de venta secuencial

### 4. Boletas e Impresión

-   PDF A4 corporativo
-   Ticket térmico 80mm
-   Código QR de verificación
-   Descarga directa desde navegador

### 5. Gestión de Créditos

-   Calendario de cuotas
-   Registro de pagos parciales
-   Cálculo automático de saldos
-   Estados: Pendiente, Al día, Vencido, Liquidado
-   Historial completo de movimientos

### 6. Reportes Estadísticos

-   **Ventas por Fecha**: Listado completo con totales
-   **Ventas por Método de Pago**: Agrupado con estadísticas
-   **Créditos por Estado**: Análisis de cartera
-   **Productos Más Vendidos**: Top ranking configurable
-   **Clientes Top**: Mejores compradores
-   **Inventario Crítico**: Alerta de stock bajo
-   Exportación PDF de todos los reportes

### 7. Control de Inventario

-   Kardex automático (entradas/salidas/ajustes)
-   Trazabilidad completa
-   Alertas de stock mínimo
-   Referencia cruzada con ventas

### 8. Panel de Administración

-   Dashboard con gráficos en tiempo real
-   Gestión de usuarios y roles
-   Configuración de temas
-   Contador de visitas por página
-   Perfil de usuario

---

## 🎨 Sistema de Temas y Accesibilidad

### Temas Disponibles

3 temas predefinidos con variantes claro/oscuro:

1. **Azul Clásico** (por defecto)
2. **Esmeralda Moderno**
3. **Púrpura Elegante**

Cambio de tema desde el navbar sin recarga de página, persistencia en localStorage.

### Accesibilidad WCAG 2.1

-   ✅ **Alto Contraste:** Relación de contraste 4.5:1 en todos los temas
-   ✅ **Tamaños de Fuente:** Escalables y legibles (mínimo 14px)
-   ✅ **Navegación por Teclado:** Todos los elementos interactivos accesibles con Tab
-   ✅ **ARIA Labels:** Etiquetas descriptivas en botones, enlaces y formularios
-   ✅ **Indicadores de Foco:** Visible en todos los elementos interactivos
-   ✅ **Textos Alternativos:** Imágenes con atributos alt descriptivos

---

## 🔒 Roles y Permisos

| Funcionalidad          | Propietario | Vendedor | Contador | Cliente |
| ---------------------- | ----------- | -------- | -------- | ------- |
| Dashboard              | ✅          | ✅       | ✅       | ✅      |
| Gestión de Productos   | ✅          | ✅       | ❌       | ❌      |
| Catálogo (ver)         | ✅          | ✅       | ✅       | ✅      |
| Carrito de Compras     | ✅          | ✅       | ✅       | ✅      |
| Crear Ventas           | ✅          | ✅       | ❌       | ❌      |
| Ver Ventas             | ✅          | ✅       | ✅       | ❌      |
| Gestión de Créditos    | ✅          | ✅       | ✅       | ❌      |
| Mis Créditos (cliente) | ❌          | ❌       | ❌       | ✅      |
| Reportes               | ✅          | ✅       | ❌       | ❌      |
| Gestión de Usuarios    | ✅          | ❌       | ❌       | ❌      |

---

## 🛠️ Mantenimiento y Comandos Personalizados

### Backup de Base de Datos

Comando Artisan para generar backups automáticos de PostgreSQL:

```bash
php artisan backup:db
```

**Descripción:**

-   Genera archivo `.sql` en `storage/app/backups/`
-   Formato de nombre: `backup_YYYYMMDD_HHMM.sql`
-   Usa `pg_dump` de PostgreSQL
-   Valida existencia de `pg_dump` antes de ejecutar
-   Muestra tamaño del archivo generado

**Backup Automático:**

-   Linux (crontab): Configurar en `/etc/crontab` (ver [DEPLOY.md](docs/DEPLOY.md))
-   Windows: Task Scheduler (ver [DEPLOY.md](docs/DEPLOY.md))

### Otros Comandos Útiles

```bash
# Optimización para producción
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar cachés
php artisan optimize:clear

# Crear enlace simbólico de storage
php artisan storage:link
```

---

## 🚀 Comandos Artisan Personalizados

```bash
# Backup de base de datos PostgreSQL
php artisan backup:db
# Genera archivo .sql en storage/app/backups/backup_YYYYMMDD_HHMM.sql
```

---

## ⚠️ Disclaimer - Funcionalidades Pendientes

> **Nota**: Las siguientes funcionalidades están **simuladas** o **no completamente implementadas**:

-   ❌ **Integración Real con Pasarela de Pago QR** (PagoFácil/Tigo Money): Actualmente se genera un UUID como código QR de simulación. La arquitectura está lista para integrar PagoFácil usando `PasarelaPagoService.php`, solo falta obtener credenciales de producción.
-   ❌ **Envío de Correos Electrónicos**: Notificaciones de ventas, boletas y recordatorios de pago pendientes. Configuración SMTP lista en `.env`.
-   ❌ **Colas de Trabajo (Queues)**: Procesamiento asíncrono de tareas pesadas. Supervisor puede configurarse según [DEPLOY.md](docs/DEPLOY.md).

**Importante sobre Bootstrap 5:**

-   ✅ Este proyecto usa **100% Bootstrap 5.3.8**, NO se utilizó Tailwind CSS
-   ✅ Todas las interfaces están construidas con clases de Bootstrap
-   ✅ Temas personalizados usando variables CSS de Bootstrap

Estas funcionalidades pueden implementarse fácilmente extendiendo los servicios existentes.

---

## 📦 Estructura del Proyecto

```
tienda-elena/
├── app/
│   ├── Actions/                # Jetstream Actions
│   ├── Console/
│   │   └── Commands/
│   │       └── BackupDatabase.php  # Comando backup:db
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CartController.php
│   │   │   ├── CatalogController.php
│   │   │   ├── CreditoController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── MenuController.php
│   │   │   ├── PagoController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SearchController.php
│   │   │   └── VentaController.php
│   │   ├── Middleware/
│   │   │   ├── TrackPageVisits.php
│   │   │   └── RoleMiddleware.php
│   │   └── Requests/           # Form Requests
│   ├── Models/                 # Eloquent Models (User, Producto, Venta, Credito, Pago, etc.)
│   └── Services/               # Lógica de Negocio
│       ├── CreditService.php
│       ├── OrderService.php
│       ├── PageVisitService.php
│       ├── PasarelaPagoService.php
│       ├── PaymentService.php
│       ├── ProductService.php
│       ├── PromotionService.php
│       └── ReportService.php
├── database/
│   ├── migrations/             # 20+ migraciones (users, productos, ventas, creditos, pagos, kardex, etc.)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── UserSeeder.php
│       ├── CategoriaSeeder.php
│       └── ProductoSeeder.php
├── docs/
│   ├── INSTALL.md              # Guía de instalación local
│   └── DEPLOY.md               # Guía de despliegue en servidores
├── resources/
│   ├── js/
│   │   ├── app.js              # Entrada principal Vue
│   │   ├── bootstrap.js
│   │   ├── Components/         # Componentes Vue
│   │   │   ├── Cart/
│   │   │   │   └── CartDropdown.vue
│   │   │   └── (otros componentes)
│   │   ├── composables/        # Composables Vue
│   │   │   └── useCart.js
│   │   ├── Layouts/
│   │   │   └── AppLayout.vue   # Layout principal con navbar, sidebar, temas
│   │   └── Pages/              # Páginas Vue (Inertia)
│   │       ├── Auth/           # Login, Register
│   │       ├── Cart/
│   │       │   └── Index.vue
│   │       ├── Catalog/
│   │       │   ├── Index.vue
│   │       │   └── Show.vue
│   │       ├── **Categorias/**     # **CRUD Categorías (Nuevo)**
│   │       │   ├── **Index.vue**   # **Tabla paginada con búsqueda**
│   │       │   ├── **Create.vue**  # **Formulario creación**
│   │       │   ├── **Edit.vue**    # **Formulario edición**
│   │       │   └── **Show.vue**    # **Detalle con productos**
│   │       ├── Credits/
│   │       │   ├── Index.vue
│   │       │   ├── Show.vue
│   │       │   └── MisCreditos.vue
│   │       ├── Dashboard.vue
│   │       ├── Pagos/
│   │       │   ├── Index.vue
│   │       │   └── Create.vue
│   │       ├── **Productos/**      # **CRUD Productos (Nuevo)**
│   │       │   ├── **Index.vue**   # **Tabla con lazy loading imágenes**
│   │       │   ├── **Create.vue**  # **Formulario con upload imagen**
│   │       │   ├── **Edit.vue**    # **Edición con reemplazo imagen**
│   │       │   └── **Show.vue**    # **Detalle formateado**
│   │       ├── **Promociones/**    # **CRUD Promociones (Nuevo)**
│   │       │   ├── **Index.vue**   # **Tabla con estado activa/inactiva**
│   │       │   ├── **Create.vue**  # **Selección múltiple productos/categorías**
│   │       │   ├── **Edit.vue**    # **Sincronización relaciones**
│   │       │   └── **Show.vue**    # **Contador productos/categorías**
│   │       ├── Reportes/
│   │       │   ├── Index.vue
│   │       │   └── Show.vue
│   │       └── Ventas/
│   │           └── Show.vue
│   └── views/                  # Vistas Blade
│       ├── app.blade.php       # Layout principal
│       ├── invoices/
│       │   ├── pdf.blade.php   # Boleta A4
│       │   └── ticket.blade.php # Ticket térmico
│       └── reports/            # 6 plantillas PDF de reportes
│           ├── ventas-fecha.blade.php
│           ├── ventas-metodo.blade.php
│           ├── creditos-estado.blade.php
│           ├── productos-vendidos.blade.php
│           ├── clientes-top.blade.php
│           └── inventario-critico.blade.php
├── routes/
│   ├── web.php                 # Rutas principales (100+ rutas)
│   ├── api.php
│   └── console.php
├── storage/
│   └── app/
│       ├── backups/            # Backups generados por backup:db
│       └── public/             # Imágenes de productos
├── public/                     # Assets compilados por Vite
├── .env.example                # Plantilla de configuración
├── composer.json               # Dependencias PHP
├── package.json                # Dependencias JavaScript
├── vite.config.js              # Configuración Vite
└── README.md                   # Este archivo
```

---

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crear rama de feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abrir Pull Request

---

## 📝 REGISTRO DE CAMBIOS RECIENTES

### **Versión 2.1.0 - Implementación CRUD Completos** (2025-11-25)

#### ✅ Funcionalidades Implementadas

**1. CRUD de Productos (100% funcional)**

-   ✅ Controller completo con autorización Policy y validaciones Form Request
-   ✅ Policy dinámica consultando `menu_items` por rol (Propietario CRUD completo, Vendedor solo lectura)
-   ✅ Validaciones: código único, precio > 0, stock ≥ 0, imagen (max 2MB, jpeg/png/webp)
-   ✅ Upload de imágenes a `storage/productos` con eliminación automática al actualizar/eliminar
-   ✅ Vistas Vue: Index (tabla paginada 15/pág, búsqueda, lazy loading imágenes), Create (preview imagen), Edit (reemplazo imagen), Show (detalle formateado)
-   ✅ Eager loading de categoría para evitar problema N+1
-   ✅ Formateo de precio en bolivianos (Bs.), badge de stock con colores (verde/amarillo/rojo)

**2. CRUD de Categorías (100% funcional)**

-   ✅ Controller con autorización Policy y validaciones Form Request
-   ✅ Policy evita eliminar categorías con productos asociados (`productos_count > 0`)
-   ✅ Validaciones: nombre único, descripción opcional (max 500 caracteres)
-   ✅ Vistas Vue: Index (contador de productos por categoría), Create/Edit (formulario simple), Show (detalle + botón "Ver Productos")
-   ✅ Eager loading con `withCount('productos')` para rendimiento

**3. CRUD de Promociones (100% funcional)**

-   ✅ Controller con relaciones many-to-many (productos/categorías) usando attach/sync/detach
-   ✅ Policy dinámica por rol, validaciones de fechas (fecha_fin > fecha_inicio, descuento 0-100%)
-   ✅ Vistas Vue: Index (badge activa/inactiva calculado dinámicamente), Create/Edit (selección múltiple Ctrl+Click), Show (contador de aplicación)
-   ✅ Eager loading de productos y categorías en listados

**4. CRUD de Usuarios (Backend 100% - Vistas pendientes)**

-   ✅ Controller completo con hash de contraseñas (bcrypt) y sincronización de roles
-   ✅ Policy evita auto-eliminación (`$user->id !== $model->id`)
-   ✅ Validaciones: CI único, email único, password confirmado (min 8 chars), roles requeridos
-   ✅ Búsqueda por nombre/email/CI, paginación
-   ⚠️ **Pendiente:** Crear 4 vistas Vue (Index/Create/Edit/Show) siguiendo patrón de Categorías

#### 🔧 Optimizaciones Técnicas

**Eager Loading (Prevención N+1)**

-   ✅ `Producto::with('categoria')` en ProductoController
-   ✅ `Categoria::withCount('productos')` en CategoriaController
-   ✅ `Promocion::with(['productos', 'categorias'])` en PromocionController
-   ✅ `User::with('roles')` en UserController

**Lazy Loading de Imágenes**

-   ✅ `<img loading="lazy">` en Index.vue de Productos para optimizar carga inicial

**Sistema de Notificaciones**

-   ✅ Componente `FlashNotification.vue` (Toast con auto-hide 5seg, posición fixed top-right)
-   ✅ Integración en `HandleInertiaRequests` compartiendo `flash.success` y `flash.error`
-   ✅ Watch de `page.props.flash` en todas las vistas CRUD

**Permisos Compartidos Globalmente**

-   ✅ `HandleInertiaRequests` comparte `auth.permissions` usando Policies
-   ✅ Botones de acción condicionales en vistas: `v-if="$page.props.auth.permissions?.productos?.create"`
-   ✅ Verificación doble: visual (ocultar botones) + funcional (middleware + Policy)

#### 📦 Archivos Creados/Modificados

**Backend (20 archivos):**

-   `app/Policies/` → ProductoPolicy, CategoriaPolicy, PromocionPolicy, UserPolicy (4 nuevos)
-   `app/Http/Requests/` → Store/Update para Producto, Categoria, Promocion, User (8 nuevos)
-   `app/Http/Controllers/` → ProductoController, CategoriaController, PromocionController, UserController (4 actualizados)
-   `app/Providers/AuthServiceProvider.php` → Registro de 4 Policies (1 actualizado)
-   `app/Http/Middleware/HandleInertiaRequests.php` → Permisos + Flash (1 actualizado)

**Frontend (13 archivos Vue):**

-   `resources/js/Pages/Productos/` → Index, Create, Edit, Show (4 nuevos)
-   `resources/js/Pages/Categorias/` → Index, Create, Edit, Show (4 nuevos)
-   `resources/js/Pages/Promociones/` → Index, Create, Edit, Show (4 nuevos)
-   `resources/js/Components/FlashNotification.vue` (1 nuevo)

**Documentación (2 archivos):**

-   `ESTRUCTURA.md` → Actualizado con nuevos controllers, policies, requests, vistas
-   `README.md` → Actualizado CU1, CU2, CU6, arquitectura, registro de cambios

#### 🎯 Próximos Pasos

1. **Crear vistas Vue de Users** (4 archivos: Index/Create/Edit/Show) - 15 min estimados
2. **Pruebas funcionales** en navegador con diferentes roles - 30 min
3. **Ajustes de validación** si hay errores en producción - 15 min
4. **Documentar en video** flujos CRUD por rol - 20 min

#### 🔗 Rutas Protegidas Implementadas

```php
// Propietario (acceso completo)
/productos, /productos/create, /productos/{id}/edit, /productos/{id}
/categorias, /categorias/create, /categorias/{id}/edit, /categorias/{id}
/promociones, /promociones/create, /promociones/{id}/edit, /promociones/{id}
/users, /users/create, /users/{id}/edit, /users/{id}

// Vendedor (solo lectura productos)
/productos, /productos/{id} (sin botones crear/editar/eliminar)

// Cliente (sin acceso a gestión)
- Redirección automática al Dashboard si intenta acceder
```

---

## 📄 Licencia

Este proyecto es de código abierto bajo la [Licencia MIT](LICENSE).

---

## 📞 Soporte

¿Problemas o preguntas? Consulta:

-   [Guía de Instalación](docs/INSTALL.md)
-   [Guía de Despliegue](docs/DEPLOY.md)
-   Issues en GitHub

---

**Desarrollado con ❤️ usando Laravel 11 + Inertia.js + Vue 3 + Bootstrap 5**
