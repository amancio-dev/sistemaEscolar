<?php

namespace App\Http\Controllers;

use App\Actions\RegistrarChamadaEmLote;
use App\Http\Requests\StoreChamadaEmLoteRequest;
use App\Models\DisciplinaProfessor;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ChamadaEmLoteController extends Controller
{
    public function create(Request $request): View
    {
        $alocacoes = $this->alocacoesDisponiveis($request->user())
            ->get()
            ->sortBy(fn (DisciplinaProfessor $alocacao): string => implode('|', [
                $alocacao->turma?->nome,
                $alocacao->disciplina?->nome,
                $alocacao->professor?->nome,
            ]), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($request->integer('turma_id') > 0 && $request->integer('alocacao_id') === 0) {
            $alocacoes = $alocacoes
                ->where('turma_id', $request->integer('turma_id'))
                ->values();
        }

        $dataAula = $this->dataDaConsulta((string) $request->query('data_aula', ''));
        $alocacao = $this->alocacaoSelecionada($request, $alocacoes);
        $matriculas = collect();
        $frequenciasExistentes = collect();

        if ($alocacao) {
            $matriculas = Matricula::query()
                ->with('aluno')
                ->where('turma_id', $alocacao->turma_id)
                ->where('situacao', 'ativa')
                ->whereHas('aluno', fn (Builder $query) => $query->where('situacao', 'ativo'))
                ->get()
                ->sortBy(fn (Matricula $matricula): string => $matricula->aluno?->nome ?? '', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            $frequenciasExistentes = Frequencia::query()
                ->where('disciplina_id', $alocacao->disciplina_id)
                ->where('turma_id', $alocacao->turma_id)
                ->whereDate('data_aula', $dataAula)
                ->whereIn('aluno_id', $matriculas->pluck('aluno_id'))
                ->get()
                ->keyBy('aluno_id');
        }

        return view('frequencias.chamada', compact(
            'alocacoes',
            'alocacao',
            'dataAula',
            'matriculas',
            'frequenciasExistentes',
        ));
    }

    public function store(
        StoreChamadaEmLoteRequest $request,
        RegistrarChamadaEmLote $registrarChamada,
    ): RedirectResponse {
        $validated = $request->validated();
        $alocacao = DisciplinaProfessor::query()->findOrFail($validated['alocacao_id']);
        $quantidade = $registrarChamada->execute(
            $alocacao,
            $validated['data_aula'],
            $validated['frequencias'],
            $request->user(),
        );

        return redirect()
            ->route('frequencias.chamada', [
                'alocacao_id' => $alocacao->getKey(),
                'data_aula' => $validated['data_aula'],
            ])
            ->with('success', "Chamada salva para {$quantidade} alunos.");
    }

    private function alocacoesDisponiveis(User $user): Builder
    {
        return DisciplinaProfessor::query()
            ->with(['professor', 'disciplina', 'turma'])
            ->whereHas('professor', fn (Builder $query) => $query->where('situacao', 'ativo'))
            ->whereHas('disciplina', fn (Builder $query) => $query->where('situacao', 'ativa'))
            ->whereHas('turma', fn (Builder $query) => $query->where('situacao', 'ativa'))
            ->when($user->tipo_usuario === 'professor', fn (Builder $query) => $query
                ->whereHas('professor', fn (Builder $query) => $query->where('user_id', $user->getKey())));
    }

    /** @param Collection<int, DisciplinaProfessor> $alocacoes */
    private function alocacaoSelecionada(Request $request, Collection $alocacoes): ?DisciplinaProfessor
    {
        $id = $request->integer('alocacao_id');

        if ($id > 0) {
            abort_unless($alocacao = $alocacoes->firstWhere('id_disciplina_professor', $id), 403,
                'Você não possui acesso a esta alocação docente.');

            return $alocacao;
        }

        $turmaId = $request->integer('turma_id');
        $alocacoesDaTurma = $alocacoes->where('turma_id', $turmaId);

        return $turmaId > 0 && $alocacoesDaTurma->count() === 1
            ? $alocacoesDaTurma->first()
            : null;
    }

    private function dataDaConsulta(string $value): string
    {
        if ($value === '') {
            return today()->toDateString();
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();

            return $date->isFuture() ? today()->toDateString() : $date->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }
}
