<div class="form-section">
    <div class="form-section-heading"><span>1</span>
        <div>
            <h2>Dados da avaliação</h2>
            <p>Aluno, disciplina, turma, professor e resultado.</p>
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
        <label class="field"><span>Período <b>*</b></span><select name="periodo" required>
                <option value="">Selecione</option>
                @foreach (['primeiro_bimestre' => '1º bimestre', 'segundo_bimestre' => '2º bimestre', 'terceiro_bimestre' => '3º bimestre', 'quarto_bimestre' => '4º bimestre'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('periodo', $record->periodo ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
        <label class="field"><span>Avaliação <b>*</b></span><input name="avaliacao"
                value="{{ old('avaliacao', $record->avaliacao ?? '') }}" maxlength="100"
                placeholder="Ex.: Prova bimestral" required></label>
        <label class="field"><span>Nota <b>*</b></span><input type="number" name="valor"
                value="{{ old('valor', $record->valor ?? '') }}" min="0" max="10" step="0.01"
                required></label>
    </div>
</div>
