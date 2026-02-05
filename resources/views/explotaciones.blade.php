<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Explotaciones</title>
</head>
<body>
   <h1>Explotaciones</h1>

@foreach ($explotaciones as $explotacion)
    <p>{{ $explotacion->nombre }} - {{ $explotacion->descripcion }}</p>
@endforeach

<h1>Total explotaciones</h1>

<h1>{{$numExplo}}</h1>
</body>
</html>
