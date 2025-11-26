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
        Schema::create('creditos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('venta_id');
            $table->decimal('monto_credito', 10, 2);
            $table->decimal('interes', 10, 2);
            $table->integer('cuotas_total');
            $table->integer('dias_mora')->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('monto_pendiente', 10, 2);
            $table->date('fecha_otorgamiento');
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagado', 'moroso'])->default('pendiente');
            $table->timestamps();

            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
