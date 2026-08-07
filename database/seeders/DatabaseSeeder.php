<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $userIds = [];
        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Administrador Escola',
            'email' => 'admin@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'administrador',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Professora Ana',
            'email' => 'ana.prof@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'professor',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Professor Carlos',
            'email' => 'carlos.prof@escola.com',
            'situacao' => 'ativo',
            'tipo_usuario' => 'professor',
            'password' => Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Aluno João',
            'email' => 'joao.aluno@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Aluno Maria',
            'email' => 'maria.aluno@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds[] = DB::table('users')->insertGetId([
            'name' => 'Aluno Pedro',
            'email' => 'pedro.aluno@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $professorIds = [];
        $professorIds[] = DB::table('professores')->insertGetId([
            'user_id' => $userIds[1],
            'nome' => 'Ana Souza',
            'cpf' => '123.456.789-00',
            'telefone' => '(11) 99999-1111',
            'email' => 'ana.souza@escola.com',
            'endereco' => 'Rua das Flores, 100',
            'formacao' => 'Licenciatura em Matemática',
            'especialidade' => 'Matemática',
            'data_contratacao' => '2024-01-10',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $professorIds[] = DB::table('professores')->insertGetId([
            'user_id' => $userIds[2],
            'nome' => 'Carlos Mendes',
            'cpf' => '987.654.321-00',
            'telefone' => '(11) 98888-2222',
            'email' => 'carlos.mendes@escola.com',
            'endereco' => 'Rua do Sol, 200',
            'formacao' => 'Licenciatura em História',
            'especialidade' => 'História',
            'data_contratacao' => '2023-08-01',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $alunoIds = [];
        $alunoIds[] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds[3],
            'numero_matricula' => '2026001',
            'nome' => 'João Pereira',
            'cpf' => '111.222.333-44',
            'data_nascimento' => '2012-05-14',
            'telefone' => '(11) 97777-0001',
            'email' => 'joao.pereira@escola.com',
            'endereco' => 'Rua das Palmeiras, 10',
            'nome_responsavel' => 'Marta Pereira',
            'telefone_responsavel' => '(11) 98888-0001',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $alunoIds[] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds[4],
            'numero_matricula' => '2026002',
            'nome' => 'Maria Silva',
            'cpf' => '222.333.444-55',
            'data_nascimento' => '2011-09-02',
            'telefone' => '(11) 97777-0002',
            'email' => 'maria.silva@escola.com',
            'endereco' => 'Rua das Orquídeas, 22',
            'nome_responsavel' => 'Ricardo Silva',
            'telefone_responsavel' => '(11) 98888-0002',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $alunoIds[] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds[5],
            'numero_matricula' => '2026003',
            'nome' => 'Pedro Almeida',
            'cpf' => '333.444.555-66',
            'data_nascimento' => '2010-12-20',
            'telefone' => '(11) 97777-0003',
            'email' => 'pedro.almeida@escola.com',
            'endereco' => 'Rua do Cedro, 33',
            'nome_responsavel' => 'Lúcia Almeida',
            'telefone_responsavel' => '(11) 98888-0003',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $turmaIds = [];
        $turmaIds[] = DB::table('turmas')->insertGetId([
            'nome' => '301A',
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'professor_responsavel_id' => $professorIds[0],
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $turmaIds[] = DB::table('turmas')->insertGetId([
            'nome' => '302B',
            'serie' => 'Ensino Fundamental',
            'turno' => 'vespertino',
            'sala' => '202',
            'ano_letivo' => 2026,
            'limite_alunos' => 25,
            'professor_responsavel_id' => $professorIds[1],
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds = [];
        $disciplinaIds[] = DB::table('disciplinas')->insertGetId([
            'nome' => 'Matemática',
            'codigo' => 'MAT101',
            'descricao' => 'Disciplina de matemática básica',
            'carga_horaria' => 80,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds[] = DB::table('disciplinas')->insertGetId([
            'nome' => 'História',
            'codigo' => 'HIS101',
            'descricao' => 'Disciplina de história geral',
            'carga_horaria' => 60,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds[] = DB::table('disciplinas')->insertGetId([
            'nome' => 'Português',
            'codigo' => 'POR101',
            'descricao' => 'Disciplina de português',
            'carga_horaria' => 70,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($alunoIds as $index => $alunoId) {
            DB::table('matriculas')->insert([
                'aluno_id' => $alunoId,
                'turma_id' => $turmaIds[$index % 2],
                'data_matricula' => '2026-02-01',
                'ano_letivo' => 2026,
                'situacao' => 'ativa',
                'observacoes' => 'Matrícula inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($alunoIds as $index => $alunoId) {
            $disciplinaId = $disciplinaIds[$index % 3];
            DB::table('notas')->insert([
                'aluno_id' => $alunoId,
                'disciplina_id' => $disciplinaId,
                'turma_id' => $turmaIds[$index % 2],
                'professor_id' => $professorIds[$index % 2],
                'periodo' => $index === 0 ? 'primeiro_bimestre' : 'segundo_bimestre',
                'avaliacao' => 'Prova escrita',
                'valor' => 8.5 + $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($disciplinaIds as $index => $disciplinaId) {
            DB::table('disciplina_professor')->insert([
                'professor_id' => $professorIds[$index % 2],
                'disciplina_id' => $disciplinaId,
                'turma_id' => $turmaIds[$index % 2],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($alunoIds as $index => $alunoId) {
            DB::table('frequencias')->insert([
                'aluno_id' => $alunoId,
                'disciplina_id' => $disciplinaIds[$index % 3],
                'turma_id' => $turmaIds[$index % 2],
                'professor_id' => $professorIds[$index % 2],
                'data_aula' => '2026-08-03',
                'situacao' => $index === 1 ? 'ausente' : 'presente',
                'justificativa' => $index === 1 ? 'Consulta médica' : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
