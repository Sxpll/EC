@extends('layouts.app')

@section('content')
<div class="container-admin">
    <div class="card-admin">
        <div class="card-header">
            <h1>Manage Users</h1>
            <button id="openModalBtn" class="btn btn-success">Add User</button>
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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover">
                <thead>
    <tr>
        <th>Name</th>
        <th>Last Name</th> <!-- Dodane -->
        <th>Email</th>
        <th>Password</th>
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
                    <input type="text" name="lastname" value="{{ $user->lastname }}" class="form-control"> <!-- Dodane -->
                </td>
                <td>
                    <input type="email" name="email" value="{{ $user->email }}" class="form-control" readonly>
                </td>
                <td>
                    <input type="password" name="password" placeholder="New password (optional)" class="form-control">
                </td>
                <td>
                    <select name="role" class="form-control">
                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
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

<!-- Modal -->
// resources/views/admin/manage-users.blade.php (dodaj w odpowiednim miejscu)

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add User</h2>
        <form action="{{ route('admin.storeUser') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name:</label> <!-- Dodane -->
                <input type="text" name="lastname" id="lastname" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="isActive">Active:</label>
                <input type="checkbox" name="isActive" id="isActive" value="1">
            </div>
            <button type="submit" class="btn btn-success">Add User</button>
        </form>
    </div>
</div>


<script>
    // Skrypt do obsługi modala
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("openModalBtn");
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
@endsection
