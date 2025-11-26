<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Propietario',
                'descripcion' => 'Administrador del sistema con acceso total',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Vendedor',
                'descripcion' => 'Usuario que gestiona ventas y productos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cliente',
                'descripcion' => 'Usuario que realiza compras',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
