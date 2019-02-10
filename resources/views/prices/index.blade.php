<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Precio</th>
        <th>Fecha y Hora</th>
    </tr>
    @foreach ($prices as $product)
        <tr>
            <td>{{ $product->id}}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->created_at }}</td>
        </tr>
    @endforeach
</table>
