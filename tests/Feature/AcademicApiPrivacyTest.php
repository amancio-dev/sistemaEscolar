<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicApiPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_apis_expose_only_the_minimum_record_and_relationship_fields(): void
    {
        [$teacherUser, $professor, $student, $turma, $disciplina] = $this->createAcademicContext();

        $nota = Nota::create([
            'aluno_id' => $student->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
            'professor_id' => $professor->getKey(),
            'periodo' => 'primeiro_bimestre',
            'avaliacao' => 'Prova escrita',
            'valor' => 8.75,
        ]);
        $frequencia = Frequencia::create([
            'aluno_id' => $student->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
            'professor_id' => $professor->getKey(),
            'data_aula' => today()->subDay(),
            'situacao' => 'justificada',
            'justificativa' => 'Atendimento médico',
        ]);

        $responses = [
            [
                $this->actingAs($teacherUser)->getJson(route('api.notas.index')),
                'data.data.0',
                ['id', 'periodo', 'avaliacao', 'valor', 'aluno', 'disciplina', 'turma', 'professor'],
            ],
            [
                $this->actingAs($teacherUser)->getJson(route('api.notas.show', $nota)),
                'data',
                ['id', 'periodo', 'avaliacao', 'valor', 'aluno', 'disciplina', 'turma', 'professor'],
            ],
            [
                $this->actingAs($teacherUser)->getJson(route('api.frequencias.index')),
                'data.data.0',
                ['id', 'data_aula', 'situacao', 'justificativa', 'aluno', 'disciplina', 'turma', 'professor'],
            ],
            [
                $this->actingAs($teacherUser)->getJson(route('api.frequencias.show', $frequencia)),
                'data',
                ['id', 'data_aula', 'situacao', 'justificativa', 'aluno', 'disciplina', 'turma', 'professor'],
            ],
        ];

        foreach ($responses as [$response, $recordPath, $expectedKeys]) {
            $response->assertOk();
            $record = $response->json($recordPath);

            $this->assertSame($expectedKeys, array_keys($record));
            $this->assertSame(
                ['id', 'nome', 'numero_matricula'],
                array_keys($record['aluno']),
            );
            $this->assertSame(['id', 'nome'], array_keys($record['professor']));

            foreach ([
                $student->cpf,
                $student->email,
                $student->telefone,
                $student->endereco,
                $student->nome_responsavel,
                $student->telefone_responsavel,
                $professor->cpf,
                $professor->email,
                $professor->telefone,
                $professor->endereco,
            ] as $sensitiveValue) {
                $this->assertStringNotContainsString($sensitiveValue, $response->getContent());
            }
        }
    }

    /** @return array{User, Professor, Aluno, Turma, Disciplina} */
    private function createAcademicContext(): array
    {
        $teacherUser = User::factory()->create([
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);
        $professor = Professor::create([
            'user_id' => $teacherUser->getKey(),
            'nome' => 'Professor da API',
            'cpf' => '111.222.333-44',
            'telefone' => '(82) 99999-1000',
            'email' => 'professor.api@escola.test',
            'endereco' => 'Rua reservada do professor, 10',
            'situacao' => 'ativo',
        ]);
        $studentUser = User::factory()->create([
            'tipo_usuario' => 'aluno',
            'situacao' => 'ativo',
        ]);
        $student = Aluno::create([
            'user_id' => $studentUser->getKey(),
            'numero_matricula' => 'API2026001',
            'nome' => 'Aluno da API',
            'cpf' => '555.666.777-88',
            'data_nascimento' => '2010-01-01',
            'telefone' => '(82) 99999-2000',
            'email' => 'aluno.api@escola.test',
            'endereco' => 'Rua reservada do aluno, 20',
            'nome_responsavel' => 'Responsável reservado',
            'telefone_responsavel' => '(82) 99999-3000',
            'situacao' => 'ativo',
        ]);
        $turma = Turma::create([
            'nome' => '301 API',
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ]);
        $disciplina = Disciplina::create([
            'nome' => 'Matemática API',
            'codigo' => 'MAT-API',
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);

        DisciplinaProfessor::create([
            'professor_id' => $professor->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
        ]);
        Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $turma->getKey(),
            'data_matricula' => '2026-02-01',
            'ano_letivo' => 2026,
            'situacao' => 'ativa',
        ]);

        return [$teacherUser, $professor, $student, $turma, $disciplina];
    }
}
