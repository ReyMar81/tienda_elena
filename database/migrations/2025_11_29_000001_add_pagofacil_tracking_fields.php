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
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('pago_facil_payment_number', 120)->nullable()->after('pago_facil_transaction_id');
            $table->text('pago_facil_raw_response')->nullable()->after('pago_facil_status');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->string('pago_facil_payment_number', 120)->nullable()->after('pago_facil_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['pago_facil_payment_number', 'pago_facil_raw_response']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['pago_facil_payment_number']);
        });
    }
};

