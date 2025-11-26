<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // Verificar si ya existe el usuario admin
            $user = DB::table('users')->where('email', 'admin@tiendaelena.com')->first();
            if (!$user) {
                // Obtener el ID del rol Propietario (case-insensitive)
                $role = DB::table('roles')->whereRaw('LOWER(nombre) = ?', ['propietario'])->first();
                
                DB::table('users')->insert([
                    'nombre' => 'Admin',
                    'apellidos' => 'Sistema',
                    'email' => 'admin@tiendaelena.com',
                    'ci' => '00000000',
                    'telefono' => '70000000',
                    'password' => Hash::make('admin123'),
                    'estado' => true,
                    'role_id' => $role ? $role->id : 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Si existe pero no tiene role_id, actualizarlo
                if (!$user->role_id) {
                    $role = DB::table('roles')->whereRaw('LOWER(nombre) = ?', ['propietario'])->first();
                    DB::table('users')
                        ->where('email', 'admin@tiendaelena.com')
                        ->update(['role_id' => $role ? $role->id : 1]);
                }
            }
        });
    }
}
