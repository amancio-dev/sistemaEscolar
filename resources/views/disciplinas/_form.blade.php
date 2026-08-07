<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados da disciplina</h2>
            <p>Identificação, carga horária e critérios de aprovação.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field"><span>Nome <b>*</b></span><input name="nome"
                value="{{ old('nome', $record->nome ?? '') }}" maxlength="255" required autofocus></label>
        <label class="field"><span>Código <b>*</b></span><input name="codigo"
                value="{{ old('codigo', $record->codigo ?? '') }}" maxlength="50" placeholder="Ex.: MAT01"
                required></label>
        <label class="field"><span>Carga horária <b>*</b></span><input type="number" name="carga_horaria"
                value="{{ old('carga_horaria', $record->carga_horaria ?? '') }}" min="1" max="2000"
                required></label>
        <label class="field"><span>Média mínima <b>*</b></span><input type="number" name="media_minima"
                value="{{ old('media_minima', $record->media_minima ?? 6) }}" min="0" max="10"
                step="0.1" required></label>
        <label class="field"><span>Situação</span><select name="situacao">
                <option value="ativa" @selected(old('situacao', $record->situacao ?? 'ativa') === 'ativa')>Ativa</option>
                <option value="inativa" @selected(old('situacao', $record->situacao ?? '') === 'inativa')>Inativa</option>
            </select></label>
        <label class="field field-full"><span>Descrição</span>
            <textarea name="descricao" rows="4" placeholder="Resumo do conteúdo e dos objetivos">{{ old('descricao', $record->descricao ?? '') }}</textarea>
        </label>
    </div>
</div>
