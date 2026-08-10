@php($editing = isset($record))
<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados da matrícula</h2>
            <p>Aluno, turma, período e situação atual.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field"><span>Aluno <b>*</b></span><select name="aluno_id" required autofocus>
                <option value="">Selecione o aluno</option>
                @foreach ($alunos as $aluno)
                    <option value="{{ $aluno->id_aluno }}" @selected((string) old('aluno_id', $record->aluno_id ?? '') === (string) $aluno->id_aluno)>{{ $aluno->nome }} ·
                        {{ $aluno->numero_matricula }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Turma <b>*</b></span><select name="turma_id" required>
                <option value="">Selecione a turma</option>
                @foreach ($turmas as $turma)
                    @php($vagasDisponiveis = max(0, (int) $turma->limite_alunos - (int) $turma->matriculas_ativas_count))
                    <option value="{{ $turma->id_turma }}" @selected((string) old('turma_id', $record->turma_id ?? '') === (string) $turma->id_turma)>{{ $turma->nome }} ·
                        {{ $turma->serie }} · {{ $turma->ano_letivo }} · {{ $vagasDisponiveis }}
                        {{ $vagasDisponiveis === 1 ? 'vaga livre' : 'vagas livres' }} ·
                        {{ ucfirst($turma->situacao) }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Data da matrícula <b>*</b></span><input type="date" name="data_matricula"
                value="{{ old('data_matricula', $editing ? $record->data_matricula?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                required></label>
        <label class="field"><span>Ano letivo <b>*</b></span><input type="number" name="ano_letivo"
                value="{{ old('ano_letivo', $record->ano_letivo ?? now()->year) }}" min="2000" max="2100"
                required></label>
        <label class="field"><span>Situação <b>*</b></span><select name="situacao" required>
                @foreach (['ativa' => 'Ativa', 'trancada' => 'Trancada', 'cancelada' => 'Cancelada', 'transferida' => 'Transferida', 'concluida' => 'Concluída'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('situacao', $record->situacao ?? 'ativa') === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
        <label class="field field-full"><span>Observações</span>
            <textarea name="observacoes" rows="4">{{ old('observacoes', $record->observacoes ?? '') }}</textarea>
        </label>
    </div>
</div>
