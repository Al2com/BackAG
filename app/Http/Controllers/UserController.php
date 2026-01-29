<?php
namespace App\Http\Controllers;
use App\Models\User; // si necesitas el modelo
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function mostrar(){

      return  $user= user::all();


    }
}
