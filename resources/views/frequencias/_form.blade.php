@php($editing = isset($record))
<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados da frequência</h2>
            <p>Aluno, aula, professor e situação de presença.</p>
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
        <label class="field"><span>Disciplina <b>*</b></span><select name="disciplina_id" required>
                <option value="">Selecione a disciplina</option>
                @foreach ($disciplinas as $disciplina)
                    <option value="{{ $disciplina->id_disciplina }}" @selected((string) old('disciplina_id', $record->disciplina_id ?? '') === (string) $disciplina->id_disciplina)>
                        {{ $disciplina->nome }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Turma <b>*</b></span><select name="turma_id" required>
                <option value="">Selecione a turma</option>
                @foreach ($turmas as $turma)
                    <option value="{{ $turma->id_turma }}" @selected((string) old('turma_id', $record->turma_id ?? '') === (string) $turma->id_turma)>{{ $turma->nome }} ·
                        {{ $turma->serie }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Professor <b>*</b></span><select name="professor_id" required>
                <option value="">Selecione o professor</option>
                @foreach ($professores as $professor)
                    <option value="{{ $professor->id_professor }}" @selected((string) old('professor_id', $record->professor_id ?? '') === (string) $professor->id_professor)>{{ $professor->nome }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Data da aula <b>*</b></span><input type="date" name="data_aula"
                value="{{ old('data_aula', $editing ? $record->data_aula?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                required></label>
        <label class="field"><span>Situação <b>*</b></span><select name="situacao" required>
                @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'justificada' => 'Falta justificada', 'atrasado' => 'Atrasado'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('situacao', $record->situacao ?? 'presente') === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
        <label class="field field-full"><span>Justificativa</span>
            <textarea name="justificativa" rows="4" placeholder="Informe o motivo quando necessário">{{ old('justificativa', $record->justificativa ?? '') }}</textarea>
        </label>
    </div>
</div>
