<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration relaxes the check constraint on `cuotas.estado`
     * to allow both 'pagada' and 'pagado' so code and frontend
     * that may use either form won't violate the DB constraint.
     */
    public function up()
    {
        // Drop existing constraint if present and recreate with expanded set
        DB::statement("ALTER TABLE cuotas DROP CONSTRAINT IF EXISTS cuotas_estado_check;");
        DB::statement("ALTER TABLE cuotas ADD CONSTRAINT cuotas_estado_check CHECK (estado IN ('pendiente','vencida','pagada','pagado','anulado'));" );
    }

    /**
     * Reverse the migrations.
     *
     * Restore a conservative constraint (original systems expected 'pagado').
     */
    public function down()
    {
        DB::statement("ALTER TABLE cuotas DROP CONSTRAINT IF EXISTS cuotas_estado_check;");
        DB::statement("ALTER TABLE cuotas ADD CONSTRAINT cuotas_estado_check CHECK (estado IN ('pendiente','vencida','pagado','anulado'));" );
    }
};
