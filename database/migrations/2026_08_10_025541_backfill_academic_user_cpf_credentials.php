<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $cpfOwners = DB::table('users')
                ->whereNotNull('cpf')
                ->get(['id', 'cpf'])
                ->groupBy(fn (object $user): string => preg_replace('/\D/', '', (string) $user->cpf))
                ->map(fn ($users): array => $users->pluck('id')->map(fn ($id): int => (int) $id)->all())
                ->all();

            foreach ([
                'aluno' => 'alunos',
                'professor' => 'professores',
            ] as $userType => $academicTable) {
                $records = DB::table('users')
                    ->join($academicTable, "{$academicTable}.user_id", '=', 'users.id')
                    ->where('users.tipo_usuario', $userType)
                    ->select([
                        'users.id as user_id',
                        "{$academicTable}.cpf as academic_cpf",
                    ])
                    ->orderBy('users.id')
                    ->get();

                foreach ($records as $record) {
                    $cpfDigits = preg_replace('/\D/', '', (string) $record->academic_cpf);

                    if (strlen($cpfDigits) !== 11) {
                        continue;
                    }

                    $userId = (int) $record->user_id;
                    $otherOwners = array_diff($cpfOwners[$cpfDigits] ?? [], [$userId]);
                    $updates = [
                        'password' => Hash::make($cpfDigits),
                        'updated_at' => now(),
                    ];

                    if ($otherOwners === []) {
                        $updates['cpf'] = preg_replace(
                            '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                            '$1.$2.$3-$4',
                            $cpfDigits,
                        );
                        $cpfOwners[$cpfDigits] = [$userId];
                    }

                    DB::table('users')
                        ->where('id', $userId)
                        ->update($updates);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Os hashes anteriores não podem ser recuperados; uma reversão exige nova credencial.
    }
};
