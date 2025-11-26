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
        Schema::create('promocion_categorias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('promocion_id');
            $table->unsignedInteger('categoria_id');
            $table->boolean('aplica_mayorista')->default(false);
            $table->boolean('aplica_minorista')->default(false);
            $table->timestamps();

            $table->foreign('promocion_id')->references('id')->on('promociones')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_categorias');
    }
};
