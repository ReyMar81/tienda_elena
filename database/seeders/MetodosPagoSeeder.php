<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodosPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar registros duplicados si existen
        DB::table('metodos_pago')->truncate();
        
        $metodosPago = [
            [
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo al momento de la compra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tarjeta de Crédito',
                'descripcion' => 'Pago mediante tarjeta de crédito Visa, Mastercard o American Express',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tarjeta de Débito',
                'descripcion' => 'Pago mediante tarjeta de débito con cargo inmediato',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Transferencia Bancaria',
                'descripcion' => 'Transferencia directa a cuenta bancaria de la tienda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'QR',
                'descripcion' => 'Pago mediante código QR con billeteras digitales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Crédito',
                'descripcion' => 'Venta a crédito con pago en cuotas mensuales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('metodos_pago')->insert($metodosPago);
    }
}
