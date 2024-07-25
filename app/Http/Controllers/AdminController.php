<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function dashboard()
    {
        Log::info('AdminController: Accessing dashboard');
        return view('admin.dashboard');
    }

    public function manageUsers()
    {
        Log::info('AdminController: Accessing manageUsers');
        $users = User::all();
        return view('admin.manage-users', compact('users'));
    }

    public function updateUser(Request $request, $id)
    {
        Log::info('AdminController: Updating user');
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|string|in:user,admin',
            'is_active' => 'required|boolean',
        ]);

        $user->update($request->all());

        return redirect()->route('admin.manageUsers')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        Log::info('AdminController: Deleting user');
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.manageUsers')->with('success', 'User deleted successfully');
    }
}
