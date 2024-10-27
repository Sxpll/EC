@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Moje Kody Rabatowe</h1>

    <h2>Aktywne Kody</h2>
    @if($discountCodes->isEmpty())
    <p>Nie masz aktywnych kodów rabatowych.</p>
    @else
    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Opis</th>
                    <th>Typ</th>
                    <th>Wartość</th>
                    <th>Ważny od</th>
                    <th>Ważny do</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discountCodes as $code)
                <tr>
                    <td>{{ $code->description }}</td>
                    <td>{{ $code->type == 'fixed' ? 'Kwotowy' : 'Procentowy' }}</td>
                    <td>{{ $code->amount }}</td>
                    <td>{{ $code->valid_from ? $code->valid_from->format('Y-m-d') : 'Brak' }}</td>
                    <td>{{ $code->valid_until ? $code->valid_until->format('Y-m-d') : 'Brak' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <h2>Historia Użyć</h2>
    @if($usages->isEmpty())
    <p>Nie masz historii użyć kodów rabatowych.</p>
    @else
    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Kod</th>
                    <th>Zamówienie</th>
                    <th>Rabat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usages as $usage)
                <tr>
                    <td>{{ $usage->created_at->format('Y-m-d') }}</td>
                    <td>{{ $usage->discountCode->description }}</td>
                    <td>{{ $usage->order_id }}</td>
                    <td>{{ number_format($usage->discount_amount, 2) }} zł</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
