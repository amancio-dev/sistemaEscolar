<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('frequencias')
            ->select(['id_frequencia', 'data_aula'])
            ->orderBy('id_frequencia')
            ->chunkById(500, function ($records): void {
                foreach ($records as $record) {
                    DB::table('frequencias')
                        ->where('id_frequencia', $record->id_frequencia)
                        ->update([
                            'data_aula' => Carbon::parse($record->data_aula)
                                ->startOfDay()
                                ->toDateString(),
                        ]);
                }
            }, 'id_frequencia');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // A normalização para meia-noite preserva a data original e não precisa ser revertida.
    }
};
