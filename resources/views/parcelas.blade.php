<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h1>Hola</h1>

@foreach($parcelas as $parcela)
<p>{{$parcela->pol_parcela}}-{{$parcela->variedad}}</p>
@endforeach
<p>>Total Hanegadas{{$TotalHng}}</p>

</body>
</html>
