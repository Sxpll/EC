@extends('layouts.app')

@section('content')
<div class="container-admin">
    <div class="card-admin">
        <div class="card-header">
            <h1>Manage Users</h1>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                <script>
                    setTimeout(() => {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 2000);
                </script>
            @endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Active</th>
                            <th colspan="2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <form action="{{ route('admin.updateUser', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input type="text" name="name" value="{{ $user->name }}" class="form-control">
                                    </td>
                                    <td>
                                        <input type="email" name="email" value="{{ $user->email }}" class="form-control">
                                    </td>
                                    <td>
                                        <select name="role" class="form-control">
                                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>user</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="hidden" name="isActive" value="0">
                                        <input type="checkbox" name="isActive" value="1" {{ $user->isActive ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-success">Update</button>
                                    </td>
                                </form>
                                <td>
                                    <form action="{{ route('admin.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
