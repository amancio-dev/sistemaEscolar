@php($editing = isset($record))
<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados do aluno</h2>
            <p>Informações de identificação e contato.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field field-full"><span>Nome completo <b>*</b></span><input name="nome"
                value="{{ old('nome', $record->nome ?? '') }}" maxlength="255" required autofocus></label>
        <label class="field"><span>Número de matrícula <b>*</b></span><input name="numero_matricula"
                value="{{ old('numero_matricula', $record->numero_matricula ?? '') }}" maxlength="255" required></label>
        <label class="field"><span>CPF <b>*</b></span><input name="cpf"
                value="{{ old('cpf', $record->cpf ?? '') }}" maxlength="14" data-mask="cpf" placeholder="000.000.000-00"
                required></label>
        <label class="field"><span>Data de nascimento <b>*</b></span><input type="date" name="data_nascimento"
                value="{{ old('data_nascimento', $editing ? $record->data_nascimento?->format('Y-m-d') : '') }}"
                required></label>
        <label class="field"><span>Telefone</span><input name="telefone"
                value="{{ old('telefone', $record->telefone ?? '') }}" maxlength="20" data-mask="phone"
                placeholder="(00) 00000-0000"></label>
        <label class="field"><span>E-mail</span><input type="email" name="email"
                value="{{ old('email', $record->email ?? '') }}" maxlength="255"></label>
        <label class="field"><span>Situação</span><select name="situacao">
                <option value="ativo" @selected(old('situacao', $record->situacao ?? 'ativo') === 'ativo')>Ativo</option>
                <option value="inativo" @selected(old('situacao', $record->situacao ?? '') === 'inativo')>Inativo</option>
                <option value="transferido" @selected(old('situacao', $record->situacao ?? '') === 'transferido')>Transferido</option>
                <option value="concluido" @selected(old('situacao', $record->situacao ?? '') === 'concluido')>Concluído</option>
            </select></label>
        <label class="field field-full"><span>Endereço</span>
            <textarea name="endereco" rows="3">{{ old('endereco', $record->endereco ?? '') }}</textarea>
        </label>
    </div>
</div>
<div class="form-section">
    <div class="form-section-heading"><span>2</span>
        <div>
            <h2>Responsável</h2>
            <p>Preencha quando o aluno tiver um responsável legal.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="field"><span>Nome do responsável</span><input name="nome_responsavel"
                value="{{ old('nome_responsavel', $record->nome_responsavel ?? '') }}" maxlength="255"></label>
        <label class="field"><span>Telefone do responsável</span><input name="telefone_responsavel"
                value="{{ old('telefone_responsavel', $record->telefone_responsavel ?? '') }}" maxlength="20"
                data-mask="phone" placeholder="(00) 00000-0000"></label>
    </div>
</div>
