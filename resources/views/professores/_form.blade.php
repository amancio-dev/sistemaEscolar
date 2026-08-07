@php($editing = isset($record))
<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados do professor</h2>
            <p>Informações pessoais e profissionais.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field field-full"><span>Nome completo <b>*</b></span><input name="nome"
                value="{{ old('nome', $record->nome ?? '') }}" maxlength="255" required autofocus></label>
        <label class="field"><span>CPF <b>*</b></span><input name="cpf" value="{{ old('cpf', $record->cpf ?? '') }}"
                maxlength="14" data-mask="cpf" placeholder="000.000.000-00" required></label>
        <label class="field"><span>E-mail <b>*</b></span><input type="email" name="email"
                value="{{ old('email', $record->email ?? '') }}" maxlength="255" required></label>
        <label class="field"><span>Telefone</span><input name="telefone"
                value="{{ old('telefone', $record->telefone ?? '') }}" maxlength="20" data-mask="phone"
                placeholder="(00) 00000-0000"></label>
        <label class="field"><span>Data de contratação</span><input type="date" name="data_contratacao"
                value="{{ old('data_contratacao', $editing ? $record->data_contratacao?->format('Y-m-d') : '') }}"></label>
        <label class="field"><span>Formação</span><input name="formacao"
                value="{{ old('formacao', $record->formacao ?? '') }}" maxlength="255"
                placeholder="Ex.: Licenciatura em Matemática"></label>
        <label class="field"><span>Especialidade</span><input name="especialidade"
                value="{{ old('especialidade', $record->especialidade ?? '') }}" maxlength="255"
                placeholder="Área principal de atuação"></label>
        <label class="field"><span>Situação</span><select name="situacao">
                <option value="ativo" @selected(old('situacao', $record->situacao ?? 'ativo') === 'ativo')>Ativo</option>
                <option value="inativo" @selected(old('situacao', $record->situacao ?? '') === 'inativo')>Inativo</option>
                <option value="afastado" @selected(old('situacao', $record->situacao ?? '') === 'afastado')>Afastado</option>
            </select></label>
        <label class="field field-full"><span>Endereço</span>
            <textarea name="endereco" rows="3">{{ old('endereco', $record->endereco ?? '') }}</textarea>
        </label>
    </div>
</div>
