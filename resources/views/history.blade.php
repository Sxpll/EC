@extends('layouts.app')

@section('content')
<div class="container-admin">
    <div class="card-admin">
        <div class="card-header">
            <h1>User Changes History</h1>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Changes</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($histories as $history)
                            <tr>
                                <td>{{ $history->admin->name }}</td>
                                <td>{{ $history->user->name }}</td>
                                <td>{{ ucfirst($history->action) }}</td>
                                <td>{{ $history->changes }}</td>
                                <td>{{ $history->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
