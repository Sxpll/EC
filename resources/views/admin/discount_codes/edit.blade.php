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

    <form action="{{ route('discount_codes.update', $discountCode->id) }}" method="POST" style="overflow-y: auto; max-height: 80vh;">
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

        <!-- Wybierz kategorie -->
        <div class="form-group">
            <label for="categories">Wybierz kategorie:</label>
            <div id="category-tree"></div>
            <input type="hidden" name="categories" id="selected-categories">
        </div>

        <!-- Data ważności od -->
        <div class="form-group">
            <label for="valid_from">Ważny od:</label>
            <input type="date" name="valid_from" id="valid_from" class="form-control" value="{{ old('valid_from', optional($discountCode->valid_from)->format('Y-m-d')) }}">
        </div>

        <!-- Data ważności do -->
        <div class="form-group">
            <label for="valid_until">Ważny do:</label>
            <input type="date" name="valid_until" id="valid_until" class="form-control" value="{{ old('valid_until', optional($discountCode->valid_until)->format('Y-m-d')) }}">
        </div>

        <!-- Czy aktywny -->
        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $discountCode->is_active ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Aktywny</label>
        </div>

        <!-- Przypisz do użytkowników -->
        <div class="form-group">
            <label for="users">Przypisz do użytkowników (opcjonalnie):</label>
            <select name="users[]" id="users" class="form-control" multiple>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $discountCode->users->contains($user->id) ? 'selected' : '' }}>{{ $user->email }} - {{ $user->name }} {{ $user->lastname }}</option>
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        const categoriesData = @json($categories);

        $('#category-tree').jstree({
            'core': {
                'data': categoriesData,
                'themes': {
                    'variant': 'large',
                    'dots': false,
                    'icons': true
                }
            },
            'plugins': ["checkbox"],
            'checkbox': {
                'three_state': false,
                'whole_node': false
            },
            'multiple': true // Umożliwia wybór wielu kategorii
        });

        // Zapisz wybrane kategorie przed wysłaniem formularza
        $('form').submit(function(e) {
            const selectedCategories = $('#category-tree').jstree("get_selected");

            if (selectedCategories.length > 0) {
                $('#selected-categories').val(JSON.stringify(selectedCategories)); // Zapisuje wybrane kategorie jako JSON
            } else {
                $('#selected-categories').remove(); // Usuwa pole, jeśli brak wyboru kategorii
            }
        });
    });
</script>

@endsection
