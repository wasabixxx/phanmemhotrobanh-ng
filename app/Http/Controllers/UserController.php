<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;  // Thêm import cho Role
use App\Models\Shift; // Thêm import cho Shift
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Lấy tất cả người dùng từ cơ sở dữ liệu
        $users = User::all();

        // Trả về view với dữ liệu người dùng
        return view('users.index', compact('users'));
    }
    // Hiển thị form tạo người dùng
    public function create()
    {
        $roles = Role::all(); // Lấy tất cả vai trò
        $shifts = Shift::all(); // Lấy tất cả ca làm việc

        return view('users.create', compact('roles', 'shifts')); // Truyền cả hai biến vào view
    }

    // Lưu người dùng mới
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'email' => 'required|string|email|max:255|unique:users',
            'role_id' => 'required|exists:roles,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        // Tạo người dùng mới
        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'shift_id' => $request->shift_id,
        ]);

        return redirect()->route('users.create')->with('success', 'Người dùng đã được tạo thành công!');
    }
}
