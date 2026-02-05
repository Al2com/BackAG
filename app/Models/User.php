<?php


namespace App\Models;
use App\Models\Explotacion;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function explotaciones(){
        return $this->hasMany(Explotacion::class);// un propietario tiene muchas explotaciones
    }

    //el usuario tiene michas operaciones
    public function operaciones(){
         return $this->hasMany(Operacion::class , 'usuario_id');
    }
//Usuario con compra_productos
      public function productos(){
        return $this->belongsToMany( Producto::class,'compra_producto','user_id','producto_id')
        ->withPivot('cantidad', 'precio', 'fecha_compra');
}

}
