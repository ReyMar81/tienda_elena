<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RESUMEN DE PERMISOS POR ROL ===\n\n";

$roles = \App\Models\Role::with('menuItems')->get();

foreach ($roles as $role) {
    $menuVisible = $role->menuItems->where('orden', '<', 999);
    $menuOculto = $role->menuItems->where('orden', '>=', 999);
    
    echo "╔═══════════════════════════════════════════════════════════════\n";
    echo "║ ROL: " . strtoupper($role->nombre) . "\n";
    echo "╠═══════════════════════════════════════════════════════════════\n";
    echo "║ Items de Menú Visible: " . $menuVisible->count() . "\n";
    echo "║ Permisos de Acción: " . $menuOculto->count() . "\n";
    echo "╠═══════════════════════════════════════════════════════════════\n";
    echo "║ MENÚ VISIBLE:\n";
    
    foreach ($menuVisible->sortBy('orden') as $item) {
        echo "║   • " . $item->etiqueta . " → " . $item->ruta_laravel . "\n";
    }
    
    echo "╠═══════════════════════════════════════════════════════════════\n";
    echo "║ PERMISOS DE ACCIÓN (no visibles en menú):\n";
    
    foreach ($menuOculto->sortBy('etiqueta') as $item) {
        echo "║   • " . $item->etiqueta . " → " . $item->ruta_laravel . "\n";
    }
    
    echo "╚═══════════════════════════════════════════════════════════════\n\n";
}
