<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeederClean extends Seeder
{
    /**
     * Menú limpio - Solo items principales en sidebar
     * Las rutas CRUD (create, edit, delete, show) existen para autorización
     * pero no se muestran en el menú lateral (se acceden vía botones en las vistas)
     */
    public function run(): void
    {
        DB::table('menu_items')->truncate();

        $menuItems = [
            // ==============================================
            // PROPIETARIO (role_id: 1) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'bi-house-door-fill', 'orden' => 10, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'bi-box-seam', 'orden' => 20, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Categorías', 'ruta_laravel' => 'categorias.index', 'icono' => 'bi-tags', 'orden' => 30, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Promociones', 'ruta_laravel' => 'promociones.index', 'icono' => 'bi-percent', 'orden' => 40, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ventas', 'ruta_laravel' => 'ventas.index', 'icono' => 'bi-cart-check', 'orden' => 50, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pedidos', 'ruta_laravel' => 'pedidos.index', 'icono' => 'bi-clipboard-check', 'orden' => 60, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Usuarios', 'ruta_laravel' => 'usuarios.index', 'icono' => 'bi-people', 'orden' => 70, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Créditos', 'ruta_laravel' => 'creditos.index', 'icono' => 'bi-credit-card', 'orden' => 80, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pagos', 'ruta_laravel' => 'pagos.index', 'icono' => 'bi-cash-coin', 'orden' => 90, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Reportes', 'ruta_laravel' => 'reportes.index', 'icono' => 'bi-graph-up', 'orden' => 100, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Rutas CRUD para autorización (NO visibles en menú - para Policies)
            ['etiqueta' => 'Crear Producto', 'ruta_laravel' => 'productos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Producto', 'ruta_laravel' => 'productos.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Producto', 'ruta_laravel' => 'productos.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Categoría', 'ruta_laravel' => 'categorias.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Categoría', 'ruta_laravel' => 'categorias.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Categoría', 'ruta_laravel' => 'categorias.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Categoría', 'ruta_laravel' => 'categorias.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Promoción', 'ruta_laravel' => 'promociones.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Promoción', 'ruta_laravel' => 'promociones.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Promoción', 'ruta_laravel' => 'promociones.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Promoción', 'ruta_laravel' => 'promociones.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Usuario', 'ruta_laravel' => 'usuarios.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Usuario', 'ruta_laravel' => 'usuarios.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Usuario', 'ruta_laravel' => 'usuarios.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Usuario', 'ruta_laravel' => 'usuarios.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Crédito', 'ruta_laravel' => 'creditos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Crédito', 'ruta_laravel' => 'creditos.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Crédito', 'ruta_laravel' => 'creditos.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Registrar Pago', 'ruta_laravel' => 'pagos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Pago', 'ruta_laravel' => 'pagos.edit', 'icono' => 'bi-pencil', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Pago', 'ruta_laravel' => 'pagos.destroy', 'icono' => 'bi-trash', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Ver Pedido', 'ruta_laravel' => 'pedidos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Pedido', 'ruta_laravel' => 'pedidos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Confirmar Pedido', 'ruta_laravel' => 'pedidos.confirmar', 'icono' => 'bi-check-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Cancelar Pedido', 'ruta_laravel' => 'pedidos.cancelar', 'icono' => 'bi-x-circle', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ==============================================
            // VENDEDOR (role_id: 2) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'bi-house-door-fill', 'orden' => 10, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'bi-box-seam', 'orden' => 20, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ventas', 'ruta_laravel' => 'ventas.index', 'icono' => 'bi-cart-check', 'orden' => 30, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pedidos', 'ruta_laravel' => 'pedidos.index', 'icono' => 'bi-clipboard-check', 'orden' => 40, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Créditos', 'ruta_laravel' => 'creditos.index', 'icono' => 'bi-credit-card', 'orden' => 50, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pagos', 'ruta_laravel' => 'pagos.index', 'icono' => 'bi-cash-coin', 'orden' => 60, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Reportes', 'ruta_laravel' => 'reportes.index', 'icono' => 'bi-graph-up', 'orden' => 70, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // Rutas para autorización Vendedor
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Crédito', 'ruta_laravel' => 'creditos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Registrar Pago', 'ruta_laravel' => 'pagos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Pedido', 'ruta_laravel' => 'pedidos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Pedido', 'ruta_laravel' => 'pedidos.create', 'icono' => 'bi-plus-circle', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Confirmar Pedido', 'ruta_laravel' => 'pedidos.confirmar', 'icono' => 'bi-check-circle', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Cancelar Pedido', 'ruta_laravel' => 'pedidos.cancelar', 'icono' => 'bi-x-circle', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // ==============================================
            // CLIENTE (role_id: 3) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'bi-house-door-fill', 'orden' => 10, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'bi-box-seam', 'orden' => 20, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Promociones', 'ruta_laravel' => 'promociones.index', 'icono' => 'bi-percent', 'orden' => 30, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mi Carrito', 'ruta_laravel' => 'carritos.index', 'icono' => 'bi-cart', 'orden' => 40, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Pedidos', 'ruta_laravel' => 'mis-pedidos.index', 'icono' => 'bi-bag-check', 'orden' => 50, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Créditos', 'ruta_laravel' => 'mis-creditos.index', 'icono' => 'bi-credit-card', 'orden' => 60, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Pagos', 'ruta_laravel' => 'mis-pagos.index', 'icono' => 'bi-cash-coin', 'orden' => 70, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Rutas para autorización Cliente
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Promoción', 'ruta_laravel' => 'promociones.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Mis Pedidos', 'ruta_laravel' => 'mis-pedidos.show', 'icono' => 'bi-eye', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('menu_items')->insert($menuItems);

        echo "\n✅ Menú limpio insertado correctamente.\n";
        echo "- Propietario: 10 módulos principales (Dashboard, Productos, Categorías, Promociones, Ventas, Pedidos, Usuarios, Créditos, Pagos, Reportes)\n";
        echo "- Vendedor: 7 módulos (Dashboard, Productos, Ventas, Pedidos, Créditos, Pagos, Reportes)\n";
        echo "- Cliente: 7 módulos (Dashboard, Productos, Promociones, Carrito, Mis Pedidos, Mis Créditos, Mis Pagos)\n";
        echo "- Las rutas CRUD (create, edit, show, destroy) existen para autorización pero NO se muestran en el menú lateral.\n";
    }
}
