@extends('layouts.app')

@section('content')
<div class="container-admin manage-users-container">
    <div class="card-admin">
        <div class="card-header">
            <h1>Manage Users</h1>
            <button id="openModalBtn" class="btn btn-success">Add User</button>
            <input type="text" id="search" placeholder="Search Users" class="form-control" style="display: inline-block; width: auto; margin-left: 20px;">
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
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table">
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->lastname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <button class="btn btn-primary btn-view" data-id="{{ $user->id }}">View</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
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
                <label for="lastname">Last Name:</label>
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

<!-- View User Modal -->
<div id="viewUserModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit User</h2>
        <form id="viewUserForm" method="POST" action="{{ route('admin.updateUser', '') }}">
            @csrf
            @method('PUT')
            <input type="hidden" id="viewUserId" name="id">
            <div class="form-group">
                <label for="viewName">Name:</label>
                <input type="text" id="viewName" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="viewLastname">Last Name:</label>
                <input type="text" id="viewLastname" name="lastname" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="viewEmail">Email:</label>
                <input type="email" id="viewEmail" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="viewPassword">Password:</label>
                <input type="password" id="viewPassword" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="viewRole">Role:</label>
                <select id="viewRole" name="role" class="form-control" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="viewActive">Active:</label>
                <input type="checkbox" id="viewActive" name="isActive" value="1">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Update User</button>
                <button type="button" id="deleteUserBtn" class="btn btn-danger" style="white-space: nowrap;">Delete User</button>
            </div>
        </form>
        
    </div>
</div>

<script>
    var addUserModal = document.getElementById("addUserModal");
    var viewUserModal = document.getElementById("viewUserModal");
    var addUserBtn = document.getElementById("openModalBtn");
    var closeBtns = document.getElementsByClassName("close");
    var viewBtns = document.getElementsByClassName("btn-view");
    var deleteUserBtn = document.getElementById("deleteUserBtn");

    addUserBtn.onclick = function() {
        addUserModal.style.display = "block";
    }

    Array.from(closeBtns).forEach(function(btn) {
        btn.onclick = function() {
            addUserModal.style.display = "none";
            viewUserModal.style.display = "none";
        }
    });

    window.onclick = function(event) {
        if (event.target == addUserModal) {
            addUserModal.style.display = "none";
        }
        if (event.target == viewUserModal) {
            viewUserModal.style.display = "none";
        }
    }

    Array.from(viewBtns).forEach(function(btn) {
        btn.onclick = function() {
            var userId = this.getAttribute("data-id");
            fetch(`/admin/user/${userId}`)
                .then(response => response.json())
                .then(data => {
                    var updateForm = document.getElementById('viewUserForm');
                    updateForm.action = `/admin/user/${userId}`;
                    document.getElementById("viewUserId").value = data.id;
                    document.getElementById("viewName").value = data.name;
                    document.getElementById("viewLastname").value = data.lastname;
                    document.getElementById("viewEmail").value = data.email;
                    document.getElementById("viewPassword").value = ''; // Clear password field
                    document.getElementById("viewRole").value = data.role;
                    document.getElementById("viewActive").checked = data.isActive;
                    deleteUserBtn.onclick = function() {
                        if (confirm('Are you sure you want to delete this user?')) {
                            fetch(`/admin/user/${userId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).then(response => {
                                if (response.ok) {
                                    location.reload();
                                } else {
                                    alert('Error deleting user');
                                }
                            });
                        }
                    };
                    viewUserModal.style.display = "block";
                });
        }
    });

    document.getElementById('search').addEventListener('input', function() {
        let query = this.value.toLowerCase();
        let rows = document.querySelectorAll('#users-table tr');
        
        rows.forEach(row => {
            let name = row.cells[0].textContent.toLowerCase();
            let lastname = row.cells[1].textContent.toLowerCase();
            let email = row.cells[2].textContent.toLowerCase();

            if (name.includes(query) || lastname.includes(query) || email.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endsection
