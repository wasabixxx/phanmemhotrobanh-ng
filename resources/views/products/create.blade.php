@extends('layouts.app')

@section('content')
<h1>Thêm Sản Phẩm Mới</h1>

<form action="{{ route('products.store') }}" method="POST">
    @csrf
    <label for="name">Tên:</label>
    <input type="text" id="name" name="name" required>

    <label for="price">Giá Bán:</label>
    <input type="text" id="price" name="price" required>

    <label for="cost_price">Giá Nhập:</label>
    <input type="text" id="cost_price" name="cost_price" required>

    <label for="quantity">Số Lượng:</label>
    <input type="number" id="quantity" name="quantity" required>

    <button type="submit">Tạo Sản Phẩm</button>
</form>
@endsection
