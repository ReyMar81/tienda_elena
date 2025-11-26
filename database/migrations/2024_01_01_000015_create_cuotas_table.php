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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('credito_id');
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->decimal('interes_cuota', 10, 2);
            $table->integer('dias_mora')->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('monto_pendiente', 10, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->timestamps();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
