<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        // Lấy danh sách tài khoản cùng với danh sách quyền trực tiếp của họ
        $users = User::latest()
            ->get()
            ->values();

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username|regex:/^[a-zA-Z0-9_.]+$/',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6',
        ], [
            'username.regex' => 'Tên đăng nhập không được chứa khoảng trắng hoặc ký tự đặc biệt.',
            'username.unique' => 'Tên đăng nhập này đã tồn tại.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return back()->with('success', 'Thêm người dùng thành công!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|regex:/^[a-zA-Z0-9_.]+$/|unique:users,username,' . $user->id,
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:6'
        ], [
            'username.regex' => 'Tên đăng nhập không được chứa khoảng trắng hoặc ký tự đặc biệt.',
            'username.unique' => 'Tên đăng nhập này đã tồn tại.',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật người dùng thành công!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Xóa người dùng thành công!');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|regex:/^[a-zA-Z0-9_.]+$/|unique:users,username,' . $user->id,
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }
}
