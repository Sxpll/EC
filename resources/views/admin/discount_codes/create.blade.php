@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Utwórz Nowy Kod Rabatowy</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('discount_codes.store') }}" method="POST">
        @csrf

        <!-- Opis -->
        <div class="form-group">
            <label for="description">Opis:</label>
            <input type="text" name="description" id="description" class="form-control" required value="{{ old('description') }}">
        </div>

        <!-- Typ rabatu -->
        <div class="form-group">
            <label for="type">Typ rabatu:</label>
            <select name="type" id="type" class="form-control" required>
                <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Kwotowy</option>
                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Procentowy</option>
            </select>
        </div>

        <!-- Wartość rabatu -->
        <div class="form-group">
            <label for="amount">Wartość rabatu:</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ old('amount') }}">
        </div>

        <!-- Data ważności od -->
        <div class="form-group">
            <label for="valid_from">Ważny od:</label>
            <input type="date" name="valid_from" id="valid_from" class="form-control" value="{{ old('valid_from') }}">
        </div>

        <!-- Data ważności do -->
        <div class="form-group">
            <label for="valid_until">Ważny do:</label>
            <input type="date" name="valid_until" id="valid_until" class="form-control" value="{{ old('valid_until') }}">
        </div>

        <!-- Czy aktywny -->
        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Aktywny</label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_single_use" id="is_single_use" class="form-check-input" value="1" {{ old('is_single_use', true) ? 'checked' : '' }}>
            <label for="is_single_use" class="form-check-label">Jednorazowy kod</label>
        </div>


        <!-- Wybór użytkowników -->
        <div class="form-group">
            <label for="users">Przypisz do użytkowników (opcjonalnie):</label>
            <select name="users[]" id="users" class="form-control" multiple>
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->email }} - {{ $user->name }} {{ $user->lastname }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">Jeśli nie wybierzesz użytkowników, kod będzie globalny i dostępny dla wszystkich.</small>
        </div>

        <!-- Przycisk submit -->
        <button type="submit" class="btn btn-primary">Utwórz Kod Rabatowy</button>
    </form>
</div>
@endsection
