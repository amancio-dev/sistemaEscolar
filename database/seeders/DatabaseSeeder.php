<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $userIds['administrador'] = DB::table('users')->insertGetId([
            'name' => 'Administrador Escola',
            'email' => 'admin@escola.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'administrador',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['ana'] = DB::table('users')->insertGetId([
            'name' => 'Professora Ana',
            'email' => 'ana.souza@escola.com',
            'cpf' => '123.456.789-00',
            'email_verified_at' => $now,
            'password' => Hash::make('12345678900'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'professor',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['carlos'] = DB::table('users')->insertGetId([
            'name' => 'Professor Carlos',
            'email' => 'carlos.mendes@escola.com',
            'cpf' => '987.654.321-00',
            'email_verified_at' => $now,
            'password' => Hash::make('98765432100'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'professor',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['beatriz'] = DB::table('users')->insertGetId([
            'name' => 'Professora Beatriz',
            'email' => 'beatriz.oliveira@escola.com',
            'cpf' => '444.555.666-77',
            'email_verified_at' => $now,
            'password' => Hash::make('44455566677'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'professor',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['joao'] = DB::table('users')->insertGetId([
            'name' => 'Aluno João',
            'email' => 'joao.pereira@escola.com',
            'cpf' => '111.222.333-44',
            'email_verified_at' => $now,
            'password' => Hash::make('11122233344'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['maria'] = DB::table('users')->insertGetId([
            'name' => 'Aluno Maria',
            'email' => 'maria.silva@escola.com',
            'cpf' => '222.333.444-55',
            'email_verified_at' => $now,
            'password' => Hash::make('22233344455'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userIds['pedro'] = DB::table('users')->insertGetId([
            'name' => 'Aluno Pedro',
            'email' => 'pedro.almeida@escola.com',
            'cpf' => '333.444.555-66',
            'email_verified_at' => $now,
            'password' => Hash::make('33344455566'),
            'situacao' => 'ativo',
            'tipo_usuario' => 'aluno',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $professorIds = [];
        $professorIds['matematica'] = DB::table('professores')->insertGetId([
            'user_id' => $userIds['ana'],
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

        $professorIds['historia'] = DB::table('professores')->insertGetId([
            'user_id' => $userIds['carlos'],
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

        $professorIds['portugues'] = DB::table('professores')->insertGetId([
            'user_id' => $userIds['beatriz'],
            'nome' => 'Beatriz Oliveira',
            'cpf' => '444.555.666-77',
            'telefone' => '(11) 97777-3333',
            'email' => 'beatriz.oliveira@escola.com',
            'endereco' => 'Rua das Acácias, 300',
            'formacao' => 'Licenciatura em Letras',
            'especialidade' => 'Português',
            'data_contratacao' => '2025-01-15',
            'situacao' => 'ativo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $alunoIds = [];
        $alunoIds['joao'] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds['joao'],
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

        $alunoIds['maria'] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds['maria'],
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

        $alunoIds['pedro'] = DB::table('alunos')->insertGetId([
            'user_id' => $userIds['pedro'],
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
        $turmaIds['301A'] = DB::table('turmas')->insertGetId([
            'nome' => '301A',
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'professor_responsavel_id' => $professorIds['matematica'],
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $turmaIds['302B'] = DB::table('turmas')->insertGetId([
            'nome' => '302B',
            'serie' => 'Ensino Fundamental',
            'turno' => 'vespertino',
            'sala' => '202',
            'ano_letivo' => 2026,
            'limite_alunos' => 25,
            'professor_responsavel_id' => $professorIds['historia'],
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds = [];
        $disciplinaIds['matematica'] = DB::table('disciplinas')->insertGetId([
            'nome' => 'Matemática',
            'codigo' => 'MAT101',
            'descricao' => 'Disciplina de matemática básica',
            'carga_horaria' => 80,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds['historia'] = DB::table('disciplinas')->insertGetId([
            'nome' => 'História',
            'codigo' => 'HIS101',
            'descricao' => 'Disciplina de história geral',
            'carga_horaria' => 60,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $disciplinaIds['portugues'] = DB::table('disciplinas')->insertGetId([
            'nome' => 'Português',
            'codigo' => 'POR101',
            'descricao' => 'Disciplina de português',
            'carga_horaria' => 70,
            'media_minima' => 6.00,
            'situacao' => 'ativa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $academicRecords = [
            [
                'aluno_id' => $alunoIds['joao'],
                'turma_id' => $turmaIds['301A'],
                'disciplina_id' => $disciplinaIds['matematica'],
                'professor_id' => $professorIds['matematica'],
                'periodo' => 'primeiro_bimestre',
                'valor' => 8.5,
                'situacao_frequencia' => 'presente',
                'justificativa' => null,
            ],
            [
                'aluno_id' => $alunoIds['maria'],
                'turma_id' => $turmaIds['302B'],
                'disciplina_id' => $disciplinaIds['historia'],
                'professor_id' => $professorIds['historia'],
                'periodo' => 'segundo_bimestre',
                'valor' => 9.5,
                'situacao_frequencia' => 'ausente',
                'justificativa' => 'Consulta médica',
            ],
            [
                'aluno_id' => $alunoIds['pedro'],
                'turma_id' => $turmaIds['301A'],
                'disciplina_id' => $disciplinaIds['portugues'],
                'professor_id' => $professorIds['portugues'],
                'periodo' => 'segundo_bimestre',
                'valor' => 10,
                'situacao_frequencia' => 'presente',
                'justificativa' => null,
            ],
        ];

        foreach ($academicRecords as $record) {
            DB::table('matriculas')->insert([
                'aluno_id' => $record['aluno_id'],
                'turma_id' => $record['turma_id'],
                'data_matricula' => '2026-02-01',
                'ano_letivo' => 2026,
                'situacao' => 'ativa',
                'observacoes' => 'Matrícula inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            [
                'professor_id' => $professorIds['matematica'],
                'disciplina_id' => $disciplinaIds['matematica'],
                'turma_id' => $turmaIds['301A'],
            ],
            [
                'professor_id' => $professorIds['historia'],
                'disciplina_id' => $disciplinaIds['historia'],
                'turma_id' => $turmaIds['302B'],
            ],
            [
                'professor_id' => $professorIds['portugues'],
                'disciplina_id' => $disciplinaIds['portugues'],
                'turma_id' => $turmaIds['301A'],
            ],
        ] as $allocation) {
            DB::table('disciplina_professor')->insert([
                ...$allocation,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($academicRecords as $record) {
            DB::table('notas')->insert([
                'aluno_id' => $record['aluno_id'],
                'disciplina_id' => $record['disciplina_id'],
                'turma_id' => $record['turma_id'],
                'professor_id' => $record['professor_id'],
                'periodo' => $record['periodo'],
                'avaliacao' => 'Prova escrita',
                'valor' => $record['valor'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('frequencias')->insert([
                'aluno_id' => $record['aluno_id'],
                'disciplina_id' => $record['disciplina_id'],
                'turma_id' => $record['turma_id'],
                'professor_id' => $record['professor_id'],
                'data_aula' => '2026-08-03',
                'situacao' => $record['situacao_frequencia'],
                'justificativa' => $record['justificativa'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
