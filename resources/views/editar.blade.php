<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Editar Explotación</title>
</head>
<body>
    <h1>Editar Explotación</h1>

    <p>Nombre: {{ $explo->nombre }}</p>
    <p>User ID: {{ $explo->user_id }}</p>
    <p>Propietario ID: {{ $explo->propietario_id }}</p>

    <form method="POST" action="{{ route('actualizar', $explo->id) }}">
        @csrf
        @method('PATCH')

        <label>Nombre:</label>
        <input type="text" name="nombre" value="{{ $explo->nombre }}">

        <button type="submit">Actualizar</button>
    </form>
</body>
</html>
