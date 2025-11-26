<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoOnlineController;
use App\Http\Controllers\PagoCuotaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'track.visits',
])->group(function () {
    
    // API para obtener menú dinámico
    Route::get('/api/menu', [MenuController::class, 'getMenuItems'])->name('api.menu');
    
    // API de búsqueda global
    Route::get('/api/search/all', [SearchController::class, 'search'])->name('api.search');
    
    // Dashboard - Todos los roles autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // GESTIÓN DE CARRITO Y PEDIDOS
    // ============================================
    
    // Carrito - Todos los usuarios autenticados
    Route::get('/mi-carrito', [CarritoController::class, 'index'])->name('carritos.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'store'])->name('carritos.store');
    Route::put('/carrito/{carritoDetalle}', [CarritoController::class, 'update'])->name('carritos.update');
    Route::delete('/carrito/{carritoDetalle}', [CarritoController::class, 'destroy'])->name('carritos.destroy');
    Route::delete('/carrito', [CarritoController::class, 'vaciar'])->name('carritos.vaciar');
    
    // Pedidos - Checkout y confirmación
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('pedidos.checkout');
    Route::post('/pedidos/procesar', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{venta}/confirmacion', [PedidoController::class, 'confirmacion'])->name('pedidos.confirmacion');

    // Carrito legacy (antiguo sistema)
    Route::prefix('carrito')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'get'])->name('get');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/sync', [CartController::class, 'sync'])->name('sync');
    });

    // Página del carrito
    Route::get('/carrito/ver', function () {
        return Inertia::render('Cart/Index');
    })->name('cart.page');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/{id}/detalles', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/contado', [VentaController::class, 'storeVentaContado'])->name('ventas.contado');
    Route::post('/ventas/credito', [VentaController::class, 'storeVentaCredito'])->middleware('role:Propietario,Vendedor')->name('ventas.credito');

    // Boletas/Facturas
    Route::get('/ventas/{id}/boleta', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/ventas/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/ventas/{id}/ticket', [InvoiceController::class, 'ticket'])->name('invoices.ticket');

    // ============================================
    // MÓDULOS CRUD COMPLETO - SOLO PROPIETARIO
    // ============================================
    Route::middleware('role:Propietario')->group(function () {
        // Productos (CRUD completo con URLs en español, solo para propietario)
        Route::get('productos/crear', [ProductoController::class, 'create'])->name('productos.create');
        Route::get('productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::delete('productos/{producto}/imagenes/{imagen}', [ProductoController::class, 'deleteImage'])->name('productos.deleteImage');
        Route::resource('productos', ProductoController::class)->only(['store', 'update', 'destroy']);

        // Categorías (CRUD completo con URLs en español)
        Route::get('categorias/crear', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::get('categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::get('categorias/{categoria}/detalles', [CategoriaController::class, 'show'])->name('categorias.show');
        Route::resource('categorias', CategoriaController::class)->except(['create', 'edit', 'show']);

        // Promociones (CRUD completo con URLs en español, solo para propietario)
        Route::get('promociones/crear', [PromocionController::class, 'create'])->name('promociones.create');
        Route::get('promociones/{promocion}/editar', [PromocionController::class, 'edit'])->name('promociones.edit');
        Route::resource('promociones', PromocionController::class)->only(['store', 'update', 'destroy']);

        // Usuarios (CRUD completo con URLs en español)
        Route::get('usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
        Route::get('usuarios/{usuario}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::get('usuarios/{usuario}/detalles', [UserController::class, 'show'])->name('usuarios.show');
        Route::post('usuarios/{usuario}/toggle-estado', [UserController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);

        // Métodos de Pago (CRUD completo)
        Route::resource('metodos-pago', MetodoPagoController::class);

        // Créditos (editar y eliminar)
        Route::get('/creditos/{id}/edit', [CreditoController::class, 'edit'])->name('creditos.edit');
        Route::put('/creditos/{id}', [CreditoController::class, 'update'])->name('creditos.update');
        Route::delete('/creditos/{id}', [CreditoController::class, 'destroy'])->name('creditos.destroy');

        // Pagos (editar y eliminar)
        Route::get('/pagos/{id}/edit', [PagoController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');
        Route::delete('/pagos/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    });

    // Permitir listar y ver productos a todos los autenticados
    Route::get('productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('productos/{producto}/detalles', [ProductoController::class, 'show'])->name('productos.show');

    // Permitir listar y ver promociones a todos los autenticados
    Route::get('promociones', [PromocionController::class, 'index'])->name('promociones.index');
    Route::get('promociones/{promocion}/detalles', [PromocionController::class, 'show'])->name('promociones.show');

    // Reportes - Solo Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/reportes', [ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/{tipo}', [ReportController::class, 'show'])->name('reportes.show');
        Route::get('/reportes/{tipo}/pdf', [ReportController::class, 'pdf'])->name('reportes.pdf');
    });

    // Créditos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
        Route::get('/creditos/{id}/detalles', [CreditoController::class, 'show'])->name('creditos.show');
        Route::post('/creditos/pago', [CreditoController::class, 'registrarPago'])->name('creditos.registrar-pago');
        Route::post('/creditos/actualizar-estados', [CreditoController::class, 'actualizarEstados'])->name('creditos.actualizar-estados');
        Route::get('/creditos/reporte/mora', [CreditoController::class, 'reporteMora'])->name('creditos.reporte-mora');
    });

    // Pagos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/{id}/detalles', [PagoController::class, 'show'])->name('pagos.show');
        Route::get('/pagos/registrar', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/pagos/buscar-cuotas', [PagoController::class, 'buscarCuotas'])->name('pagos.buscar-cuotas');
        Route::get('/pagos/historial/{cuotaId}', [PagoController::class, 'historialCuota'])->name('pagos.historial');
    });

    // Gestión de Pedidos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/pedidos', [\App\Http\Controllers\GestionPedidosController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/crear', [\App\Http\Controllers\GestionPedidosController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos', [\App\Http\Controllers\GestionPedidosController::class, 'store'])->name('pedidos.store');
        Route::get('/pedidos/{id}/detalles', [\App\Http\Controllers\GestionPedidosController::class, 'show'])->name('pedidos.show');
        Route::get('/pedidos/{id}/editar', [\App\Http\Controllers\GestionPedidosController::class, 'edit'])->name('pedidos.edit');
        Route::put('/pedidos/{id}', [\App\Http\Controllers\GestionPedidosController::class, 'update'])->name('pedidos.update');
        Route::patch('/pedidos/{id}/accion', [\App\Http\Controllers\GestionPedidosController::class, 'accion'])->name('pedidos.accion');
        Route::patch('/pedidos/{id}/marcar-enviado', [PedidoOnlineController::class, 'marcarComoEnviado'])->name('pedidos.marcar-enviado');
    });

    // Mis Créditos y Pagos - Solo Cliente
    Route::middleware('role:Cliente')->group(function () {
        // Mis Créditos
        Route::get('/mis-creditos', [\App\Http\Controllers\MisCreditosController::class, 'index'])->name('mis-creditos.index');
        Route::get('/mis-creditos/{id}/detalles', [\App\Http\Controllers\MisCreditosController::class, 'show'])->name('mis-creditos.show');
        Route::post('/mis-creditos/pago', [\App\Http\Controllers\MisCreditosController::class, 'registrarPago'])->name('mis-creditos.registrar-pago');

        Route::post('/pagos/generar-qr', [PagoController::class, 'generarQR'])->name('pagos.generar-qr');
        
        // Mis Pedidos - Solo Cliente
        Route::get('/mis-pedidos', [\App\Http\Controllers\MisPedidosController::class, 'index'])->name('mis-pedidos.index');
        Route::get('/mis-pedidos/{id}/detalles', [\App\Http\Controllers\MisPedidosController::class, 'show'])->name('mis-pedidos.show');
        
        // Mis Pagos - Solo Cliente
        Route::get('/mis-pagos', [\App\Http\Controllers\MisPagosController::class, 'index'])->name('mis-pagos.index');

        // Pedidos Online - Cliente crea pedido desde carrito
        Route::post('/carrito/realizar-pedido', [PedidoOnlineController::class, 'realizarPedido'])->name('carrito.realizar-pedido');
        
        // Pagos QR para cuotas de créditos
        Route::post('/cuotas/{id}/generar-qr', [PagoCuotaController::class, 'generarQRCuota'])->name('cuotas.generar-qr');
        Route::get('/pagos/{id}/estado', [PagoCuotaController::class, 'verificarEstadoPago'])->name('pagos.verificar-estado');
    });
});

// ============================================
// WEBHOOKS DE PAGOFACIL (SIMULADOS)
// Estas rutas NO requieren autenticación
// ============================================
Route::post('/webhook/pagofacil-simulado/venta', [PedidoOnlineController::class, 'webhookVentaSimulado'])->name('webhook.pagofacil.venta');
Route::post('/webhook/pagofacil-simulado/cuota', [PagoCuotaController::class, 'webhookCuotaSimulado'])->name('webhook.pagofacil.cuota');

// ============================================
// ENDPOINTS DE PRUEBA (SOLO DESARROLLO)
// Eliminar en producción
// ============================================
Route::post('/pagofacil-simulado/confirmar-pago', [PedidoOnlineController::class, 'confirmarPagoSimulado'])->name('pagofacil.confirmar-simulado');
Route::post('/pagofacil-simulado/confirmar-pago-cuota', [PagoCuotaController::class, 'confirmarPagoSimulado'])->name('pagofacil.confirmar-cuota-simulado');
