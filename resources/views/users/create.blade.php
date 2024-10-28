@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tạo Người Dùng Mới</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="username">Tên Đăng Nhập:</label>
            <input type="text" name="username" class="form-control" id="username" required>
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Mật Khẩu:</label>
            <input type="password" name="password" class="form-control" id="password" required>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Xác Nhận Mật Khẩu:</label>
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required>
        </div>

        <div class="form-group">
            <label for="name">Họ Tên:</label>
            <input type="text" name="name" class="form-control" id="name" required>
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Số Điện Thoại:</label>
            <input type="text" name="phone" class="form-control" id="phone">
            @error('phone')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" class="form-control" id="email" required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="role_id">Vai Trò:</label>
            <select name="role_id" class="form-control" id="role_id" required>
                <option value="">Chọn vai trò</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="shift_id">Ca Làm Việc:</label>
            <select name="shift_id" class="form-control" id="shift_id" required>
                <option value="">Chọn ca làm việc</option>
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                @endforeach
            </select>
            @error('shift_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Tạo Người Dùng</button>
    </form>
</div>
@endsection
