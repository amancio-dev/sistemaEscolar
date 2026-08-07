@if (session('success'))
    <div class="alert alert-success" role="status">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m5 12 4 4L19 6" />
        </svg>
        <span>{{ session('success') }}</span>
        <button class="alert-close" type="button" aria-label="Fechar" onclick="this.closest('.alert').remove()">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error" role="alert">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" />
        </svg>
        <span>{{ session('error') }}</span>
        <button class="alert-close" type="button" aria-label="Fechar" onclick="this.closest('.alert').remove()">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error" role="alert">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" />
        </svg>
        <div>
            <strong>Revise os campos informados:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button class="alert-close" type="button" aria-label="Fechar" onclick="this.closest('.alert').remove()">&times;</button>
    </div>
@endif
