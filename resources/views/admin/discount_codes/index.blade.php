@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Zarządzaj Kodami Rabatowymi</h1>

    <a href="{{ route('discount_codes.create') }}" class="btn btn-primary">Utwórz Nowy Kod</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Opis</th>
                <th>Typ</th>
                <th>Wartość</th>
                <th>Aktywny</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discountCodes as $code)
            <tr>
                <td>{{ $code->id }}</td>
                <td>{{ $code->description }}</td>
                <td>{{ $code->type == 'fixed' ? 'Kwotowy' : 'Procentowy' }}</td>
                <td>{{ $code->amount }}</td>
                <td>{{ $code->is_active ? 'Tak' : 'Nie' }}</td>
                <td>
                    <a href="{{ route('discount_codes.edit', $code->id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                    <form action="{{ route('discount_codes.destroy', $code->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
