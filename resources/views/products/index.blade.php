@extends('layouts.app')

@section('content')
<h1>Danh Sách Sản Phẩm</h1>
<a href="{{ route('products.create') }}">Thêm Sản Phẩm</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Giá Bán</th>
            <th>Giá Nhập</th>
            <th>Số Lượng</th>
            <th>Hành Động</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->price }}</td>
                <td>{{ $product->cost_price }}</td>
                <td>{{ $product->quantity }}</td>
                <td>
                    <a href="#">Sửa</a>
                    <form method="POST" action="#">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
