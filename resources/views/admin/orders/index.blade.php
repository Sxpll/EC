@extends('layouts.app')

@section('content')
<div class="orders-container">
    <div class="orders-card">
        <div class="orders-card-header d-flex align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="back-arrow mr-auto">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Zarządzaj Zamówieniami</h1>
        </div>
        <div class="orders-card-body">
            @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
            @endif

            <!-- Dodano kontener dla tabeli -->
            <div class="orders-table-wrapper">
                <table class="orders-table table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Nr Zamówienia</th>
                            <th>Użytkownik</th>
                            <th>Łącznie</th>
                            <th>Status</th>
                            <th>Data Zamówienia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr class="order-row" onclick="toggleOrderDetails(this)">
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name }} {{ $order->user->lastname }}</td>
                            <td>{{ number_format($order->total, 2) }} zł</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn-details btn btn-primary btn-sm">view</button>
                                <span class="expand-icon">&#9654;</span>
                            </td>
                        </tr>
                        <tr class="orders-details">
                            <td colspan="6">
                                <h5>Pozycje Zamówienia:</h5>
                                <ul>
                                    @foreach($order->orderItems as $item)
                                    <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł</li>
                                    @endforeach
                                </ul>
                                <div class="order-management">
                                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="form-inline">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <label for="status" class="mr-2">Status:</label>
                                            <select name="status" class="form-control">
                                                <option value="Oczekujące" {{ $order->status == 'Oczekujące' ? 'selected' : '' }}>Oczekujące</option>
                                                <option value="W realizacji" {{ $order->status == 'W realizacji' ? 'selected' : '' }}>W realizacji</option>
                                                <option value="W drodze" {{ $order->status == 'W drodze' ? 'selected' : '' }}>W drodze</option>
                                                <option value="Zakończone" {{ $order->status == 'Zakończone' ? 'selected' : '' }}>Zakończone</option>
                                                <option value="Anulowane" {{ $order->status == 'Anulowane' ? 'selected' : '' }}>Anulowane</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn-update btn btn-success mb-2 ml-2">Zaktualizuj Status</button>
                                        <!-- Opcjonalny przycisk anulowania
                                        <button type="button" class="btn-cancel btn btn-danger mb-2 ml-2">Anuluj Zamówienie</button>
                                        -->
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Koniec kontenera orders-table-wrapper -->

        </div>
    </div>
</div>

<script>
    function toggleOrderDetails(row) {
        const detailsRow = row.nextElementSibling;
        const icon = row.querySelector('.expand-icon');

        if (detailsRow.classList.contains('expanded')) {
            detailsRow.classList.remove('expanded');
            icon.classList.remove('rotated');
        } else {
            detailsRow.classList.add('expanded');
            icon.classList.add('rotated');
        }
    }
</script>
@endsection
