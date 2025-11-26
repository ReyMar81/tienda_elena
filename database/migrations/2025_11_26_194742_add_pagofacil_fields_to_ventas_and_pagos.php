<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campos a tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('origen', 20)->default('tienda')->after('estado'); // 'tienda' o 'online'
            $table->text('direccion_entrega')->nullable()->after('origen'); // Solo para online
            $table->string('pago_facil_transaction_id', 100)->nullable()->after('direccion_entrega');
            $table->text('pago_facil_qr_image')->nullable()->after('pago_facil_transaction_id');
            $table->string('pago_facil_status', 50)->nullable()->after('pago_facil_qr_image'); // 'pending', 'completed', 'failed'
        });

        // Agregar campos a tabla pagos
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('pago_facil_transaction_id', 100)->nullable()->after('metodo_pago_id');
            $table->text('pago_facil_qr_image')->nullable()->after('pago_facil_transaction_id');
            $table->string('pago_facil_status', 50)->nullable()->after('pago_facil_qr_image'); // 'pending', 'completed', 'failed'
            $table->text('pago_facil_raw_response')->nullable()->after('pago_facil_status'); // JSON response
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'origen',
                'direccion_entrega',
                'pago_facil_transaction_id',
                'pago_facil_qr_image',
                'pago_facil_status'
            ]);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'pago_facil_transaction_id',
                'pago_facil_qr_image',
                'pago_facil_status',
                'pago_facil_raw_response'
            ]);
        });
    }
};
