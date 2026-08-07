<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados da turma</h2>
            <p>Organização, capacidade e professor responsável.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field"><span>Nome da turma <b>*</b></span><input name="nome"
                value="{{ old('nome', $record->nome ?? '') }}" maxlength="255" placeholder="Ex.: Turma A" required
                autofocus></label>
        <label class="field"><span>Série <b>*</b></span><input name="serie"
                value="{{ old('serie', $record->serie ?? '') }}" maxlength="50" placeholder="Ex.: 2º ano"
                required></label>
        <label class="field"><span>Turno <b>*</b></span><select name="turno" required>
                <option value="">Selecione</option>
                @foreach (['matutino' => 'Matutino', 'vespertino' => 'Vespertino', 'noturno' => 'Noturno', 'integral' => 'Integral'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('turno', $record->turno ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Sala <b>*</b></span><input name="sala"
                value="{{ old('sala', $record->sala ?? '') }}" maxlength="50" required></label>
        <label class="field"><span>Ano letivo <b>*</b></span><input type="number" name="ano_letivo"
                value="{{ old('ano_letivo', $record->ano_letivo ?? now()->year) }}" min="2000" max="2100"
                required></label>
        <label class="field"><span>Limite de alunos <b>*</b></span><input type="number" name="limite_alunos"
                value="{{ old('limite_alunos', $record->limite_alunos ?? 30) }}" min="1" max="200"
                required></label>
        <label class="field"><span>Professor responsável</span><select name="professor_responsavel_id">
                <option value="">Não definido</option>
                @foreach ($professores as $professor)
                    <option value="{{ $professor->id_professor }}" @selected((string) old('professor_responsavel_id', $record->professor_responsavel_id ?? '') === (string) $professor->id_professor)>{{ $professor->nome }}
                    </option>
                @endforeach
            </select></label>
        <label class="field"><span>Situação</span><select name="situacao">
                <option value="ativa" @selected(old('situacao', $record->situacao ?? 'ativa') === 'ativa')>Ativa</option>
                <option value="inativa" @selected(old('situacao', $record->situacao ?? '') === 'inativa')>Inativa</option>
                <option value="concluida" @selected(old('situacao', $record->situacao ?? '') === 'concluida')>Concluída</option>
            </select></label>
    </div>
</div>
