<div class="form-section">
    <div class="form-section-heading">
        <span>1</span>
        <div>
            <h2>Vínculo docente</h2>
            <p>Selecione o professor, sua disciplina de atuação e a turma desta alocação.</p>
        </div>
    </div>

    <div class="form-grid">
        <label class="field">
            <span>Professor <b>*</b></span>
            <select name="professor_id" required autofocus
                @error('professor_id') aria-invalid="true" aria-describedby="professor_id-error" @enderror>
                <option value="">Selecione o professor</option>
                @foreach ($professores as $professor)
                    <option value="{{ $professor->id_professor }}" @selected((string) old('professor_id', $record->professor_id ?? '') === (string) $professor->id_professor)>
                        {{ $professor->nome }}{{ $professor->especialidade ? ' · Especialidade cadastral: '.$professor->especialidade : '' }}
                    </option>
                @endforeach
            </select>
            <small class="field-hint">A especialidade é uma informação cadastral e não representa outra disciplina nesta alocação.</small>
            @error('professor_id')
                <small class="field-hint" id="professor_id-error">{{ $message }}</small>
            @enderror
        </label>

        <label class="field">
            <span>Disciplina da alocação <b>*</b></span>
            <select name="disciplina_id" required
                @error('disciplina_id') aria-invalid="true" aria-describedby="disciplina_id-error" @enderror>
                <option value="">Selecione a disciplina</option>
                @foreach ($disciplinas as $disciplina)
                    <option value="{{ $disciplina->id_disciplina }}" @selected((string) old('disciplina_id', $record->disciplina_id ?? '') === (string) $disciplina->id_disciplina)>
                        {{ $disciplina->nome }} · {{ $disciplina->codigo }}
                    </option>
                @endforeach
            </select>
            @error('disciplina_id')
                <small class="field-hint" id="disciplina_id-error">{{ $message }}</small>
            @enderror
        </label>

        <label class="field">
            <span>Turma <b>*</b></span>
            <select name="turma_id" required
                @error('turma_id') aria-invalid="true" aria-describedby="turma_id-error" @enderror>
                <option value="">Selecione a turma</option>
                @foreach ($turmas as $turma)
                    <option value="{{ $turma->id_turma }}" @selected((string) old('turma_id', $record->turma_id ?? '') === (string) $turma->id_turma)>
                        {{ $turma->nome }} · {{ $turma->serie }} · {{ $turma->ano_letivo }} ·
                        {{ Illuminate\Support\Str::ucfirst($turma->turno) }}
                    </option>
                @endforeach
            </select>
            @error('turma_id')
                <small class="field-hint" id="turma_id-error">{{ $message }}</small>
            @enderror
        </label>
    </div>

    <small class="field-hint">Cada docente deve atuar em uma única disciplina, que pode ser vinculada a várias turmas.</small>
</div>
