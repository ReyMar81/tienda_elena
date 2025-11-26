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
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cuota_id');
            $table->unsignedInteger('metodo_pago_id');
            $table->decimal('monto', 10, 2);
            $table->decimal('recargo_extra', 10, 2)->default(0);
            $table->decimal('interes_mora_cobrado', 10, 2)->default(0);
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('cuota_id')->references('id')->on('cuotas')->onDelete('cascade');
            $table->foreign('metodo_pago_id')->references('id')->on('metodos_pago')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
