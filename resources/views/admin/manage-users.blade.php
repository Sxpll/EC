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
            <div id="alert-container"></div>
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
        
        <!-- Zakładki -->
        <div class="tabs">
            <button class="tab-link active" onclick="openTab(event, 'Info')">Info</button>
            <button class="tab-link" onclick="openTab(event, 'History')">History</button>
        </div>
        
        <div id="Info" class="tab-content active">
            <form id="viewUserForm" method="POST">
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
                <div class="form-group d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary mx-2" style="white-space: nowrap;">Update User</button>
                    <button type="button" id="deleteUserBtn" class="btn btn-danger mx-2" style="white-space: nowrap;">Delete User</button>
                </div>
            </form>
        </div>
        <div id="History" class="tab-content">
            <table id="historyTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Admin Name</th>
                        <th>Admin Last Name</th>
                        <th>Action</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    document.addEventListener("DOMContentLoaded", function() {
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
                console.log("Fetching user with ID:", userId);

                fetch(`/admin/user/${userId}`)
                    .then(response => {
                        console.log("Response status:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log("User data:", data);

                        document.getElementById("viewUserId").value = data.id;
                        document.getElementById("viewName").value = data.name;
                        document.getElementById("viewLastname").value = data.lastname;
                        document.getElementById("viewEmail").value = data.email;
                        document.getElementById("viewRole").value = data.role;
                        document.getElementById("viewActive").checked = data.isActive;

                        var viewUserForm = document.getElementById("viewUserForm");
                        viewUserForm.onsubmit = function(event) {
                            event.preventDefault();

                            var updatedUser = {
                                name: document.getElementById("viewName").value,
                                lastname: document.getElementById("viewLastname").value,
                                email: document.getElementById("viewEmail").value,
                                role: document.getElementById("viewRole").value,
                                isActive: document.getElementById("viewActive").checked,
                                _method: 'PUT',  // Laravel wymaga tego do PUT/PATCH
                                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            };

                            var password = document.getElementById("viewPassword").value;
                            if (password) {
                                updatedUser.password = password;
                            }

                            axios.post(`/admin/user/${userId}`, updatedUser)
                                .then(response => {
                                    if (response.data.success) {
                                        sessionStorage.setItem('message', 'User updated successfully');
                                        sessionStorage.setItem('messageType', 'success');
                                        location.reload();
                                    } else {
                                        alert('Error updating user');
                                        console.error("Server error:", response.data.error);
                                    }
                                })
                                .catch(error => {
                                    console.error('Update error:', error.response ? error.response.data : error);
                                    alert('Error updating user');
                                });
                        };

                        deleteUserBtn.onclick = function() {
                            if (confirm('Are you sure you want to delete this user?')) {
                                axios.delete(`/admin/user/${userId}`, {
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                }).then(response => {
                                    if (response.data.success) {
                                        sessionStorage.setItem('message', 'User deleted successfully');
                                        sessionStorage.setItem('messageType', 'success');
                                        location.reload();
                                    } else {
                                        alert('Error deleting user');
                                    }
                                }).catch(error => {
                                    console.error(error);
                                    alert('Error deleting user');
                                });
                            }
                        };

                       // Fetch user history
fetch(`/admin/user/${userId}/history`)
    .then(response => response.json())
    .then(histories => {
        const historyTableBody = document.querySelector('#historyTable tbody');
        historyTableBody.innerHTML = '';
        histories.forEach(history => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${history.admin_name}</td>
                <td>${history.admin_lastname}</td>
                <td>${history.action}</td>
                <td>${history.old_value ? history.old_value : 'N/A'}</td>
                <td>${history.new_value ? history.new_value : 'N/A'}</td>
                <td>${new Date(history.created_at).toLocaleString()}</td>
            `;
            historyTableBody.appendChild(row);
        });
    });

                        viewUserModal.style.display = "block";
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
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

        // Display message from sessionStorage
        if (sessionStorage.getItem('message')) {
            var message = sessionStorage.getItem('message');
            var messageType = sessionStorage.getItem('messageType');
            var alertBox = document.createElement('div');
            alertBox.className = 'alert alert-' + messageType;
            alertBox.textContent = message;

            document.getElementById('alert-container').prepend(alertBox);

            setTimeout(() => {
                alertBox.style.display = 'none';
                sessionStorage.removeItem('message');
                sessionStorage.removeItem('messageType');
            }, 2000);
        }
    });
</script>
@endsection
