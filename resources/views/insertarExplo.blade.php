@if ($errors->any()) {{-- esto muestra todos los errores seguidos --}}
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
<br/>
@endif

<form method="post" action="{{ route('almacenarExplo') }}">

        @csrf
        <label for="name">Nombre:</label>
        <input type="text" name="nombre" value="{{old('nombre')}}"/>
        <!--ponemos old porque en el caso que carguemos el formulario y diera error habría que volver a introducir todos los campos, así recuerda o guarda los campos que están bien-->
     {!! $errors->first('name', '<small>:message</small><br>' )!!}  <!-- así especificamos los errores debajo-->

        <label for="price">Ubicación:</label>
        <input type="text" name="ubicacion" value="{{old('ubicacion')}}"/>
     {!! $errors->first('ubicacion', '<small>:message</small><br>' )!!}

        <label for="price">Descripción:</label>
        <input type="text" name="descripcion" value="{{old('descripcion')}}"/>
     {!! $errors->first('descripcion', '<small>:message</small><br>' )!!}


     <label for="price">User_id:</label>
        <input type="text" name="user_id" value="{{old('user_id')}}"/>
     {!! $errors->first('descripcion', '<small>:message</small><br>' )!!}

     <label for="price">Propietario_id:</label>
        <input type="text" name="propietario_id" value="{{old('propietario_id')}}"/>
     {!! $errors->first('descripcion', '<small>:message</small><br>' )!!}

        <button type="submit">Crear</button>
</form>
