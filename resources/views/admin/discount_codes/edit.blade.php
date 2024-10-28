@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edytuj Kod Rabatowy</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('discount_codes.update', $discountCode->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Opis -->
        <div class="form-group">
            <label for="description">Opis:</label>
            <input type="text" name="description" id="description" class="form-control" required value="{{ old('description', $discountCode->description) }}">
        </div>

        <!-- Typ rabatu -->
        <div class="form-group">
            <label for="type">Typ rabatu:</label>
            <select name="type" id="type" class="form-control" required>
                <option value="fixed" {{ $discountCode->type == 'fixed' ? 'selected' : '' }}>Kwotowy</option>
                <option value="percentage" {{ $discountCode->type == 'percentage' ? 'selected' : '' }}>Procentowy</option>
            </select>
        </div>

        <!-- Wartość rabatu -->
        <div class="form-group">
            <label for="amount">Wartość rabatu:</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ old('amount', $discountCode->amount) }}">
        </div>

        <!-- Data ważności od -->
        <div class="form-group">
            <label for="valid_from">Ważny od:</label>
            <input type="date" name="valid_from" id="valid_from" class="form-control" value="{{ old('valid_from', $discountCode->valid_from ? $discountCode->valid_from->format('Y-m-d') : '') }}">
        </div>

        <!-- Data ważności do -->
        <div class="form-group">
            <label for="valid_until">Ważny do:</label>
            <input type="date" name="valid_until" id="valid_until" class="form-control" value="{{ old('valid_until', $discountCode->valid_until ? $discountCode->valid_until->format('Y-m-d') : '') }}">
        </div>

        <!-- Czy aktywny -->
        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $discountCode->is_active ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Aktywny</label>
        </div>

        <!-- Wybór użytkowników -->
        <div class="form-group">
            <label for="users">Przypisz do użytkowników (opcjonalnie):</label>
            <select name="users[]" id="users" class="form-control" multiple>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $discountCode->users->contains($user->id) ? 'selected' : '' }}>
                    {{ $user->email }} - {{ $user->name }} {{ $user->lastname }}
                </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Jeśli nie wybierzesz użytkowników, kod będzie globalny i dostępny dla wszystkich.</small>
        </div>

        <!-- Przycisk submit -->
        <button type="submit" class="btn btn-primary">Zapisz Zmiany</button>
    </form>
</div>
@endsection

@section('scripts')
<!-- Dodajemy Select2 dla lepszego wyboru użytkowników -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#users').select2({
            placeholder: "Wybierz użytkowników",
            allowClear: true
        });
    });
</script>
@endsection
