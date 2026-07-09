<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('unities')->orderBy('created_at', 'desc')->get();

        return view('admin.users.index', [
            'users' => $users,
            'pageTitle' => __('admin.users.title'),
            'breadcrumbs' => [
                ['label' => __('admin.users.title'), 'url' => null],
            ],
        ]);
    }

    public function show(int $id): View
    {
        $user = User::with('unities')->findOrFail($id);

        return view('admin.users.show', [
            'user' => $user,
            'pageTitle' => $user->name,
            'breadcrumbs' => [
                ['label' => __('admin.users.title'), 'url' => route('admin.users.index')],
                ['label' => $user->name, 'url' => null],
            ],
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $user->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', "User '{$user->name}' has been approved.");
    }

    public function reject(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot reject your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' has been rejected and deleted.");
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }

        $request->validate([
            'role' => 'required|in:admin,common',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', "User '{$user->name}' role has been updated to '{$request->role}'.");
    }
}
