@extends('layouts.app')

@section('content')
<h1>Danh Sách Bán Hàng</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Sản Phẩm</th>
            <th>Người Dùng</th>
            <th>Số Lượng</th>
            <th>Tổng Tiền</th>
            <th>Lợi Nhuận</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->product_id }}</td>
                <td>{{ $sale->user_id }}</td>
                <td>{{ $sale->quantity }}</td>
                <td>{{ $sale->total_price }}</td>
                <td>{{ $sale->profit }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
