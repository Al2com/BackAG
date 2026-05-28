<?php
namespace App\Http\Controllers;
use App\Models\User; // si necesitas el modelo
use Illuminate\Http\Request;

class UserController extends Controller{
   
public function mostrarUsers(){
    $usuarios = User::where('rol', 'admin')->get();//filtra solo admin 
    return response()->json(['usuarios' => $usuarios]);
}


}
