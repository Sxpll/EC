<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $totalUsers = User::count();
        $activeUsers = User::where('isActive', true)->count();
        $inactiveUsers = User::where('isActive', false)->count();

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function manageUsers()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $users = User::all();
        return view('admin.manage-users', compact('users'));
    }

    public function updateUser(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'isActive' => 'nullable|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'isActive' => $request->input('isActive') ? true : false,

        ]);

        return redirect()->route('admin.manageUsers')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.manageUsers')->with('success', 'User deleted successfully');
    }
}
