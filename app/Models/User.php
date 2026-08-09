<?php


namespace App\Models;
use App\Models\Explotacion;
use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'admin_id',
        'tema',
        'foto_perfil',
        'foto_perfil_thumb',
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
     * Rutas de foto_perfil/foto_perfil_thumb se guardan relativas al disco
     * 'public'; se añaden como URL absoluta para que el front no tenga que
     * conocer la convención de almacenamiento.
     *
     * @var list<string>
     */
    protected $appends = [
        'foto_perfil_url',
        'foto_perfil_thumb_url',
    ];

    public function getFotoPerfilUrlAttribute(): ?string
    {
        return $this->foto_perfil ? Storage::disk('public')->url($this->foto_perfil) : null;
    }

    public function getFotoPerfilThumbUrlAttribute(): ?string
    {
        return $this->foto_perfil_thumb ? Storage::disk('public')->url($this->foto_perfil_thumb) : null;
    }

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

     /***********************************************
     * ID del administrador "dueño" del inquilino actual.
     * Un admin se pertenece a sí mismo; un trabajador, a su admin.
     * Por qué: es la misma regla que usa el AdminScope. Tenerla en
     *  un sitio evita que las validaciones exists (fix 1.6) y el resto usen criterios distintos y diverjan.
     **************************************************/
    public function adminId(): int{
        return $this->rol === 'trabajador' ? (int) $this->admin_id : (int) $this->id;
    }
    public function explotaciones(){
        return $this->hasMany(Explotacion::class, 'admin_id');// un propietario tiene muchas explotaciones
    }

    //el usuario tiene michas operaciones
    public function operaciones(){
         return $this->hasMany(Operacion::class , 'usuario_id');
    }
//Usuario con compra_productos Vieja (modificacion modelo ComprasProducto)
    //   public function productos(){
    //     return $this->belongsToMany( Producto::class,'compra_producto','user_id','producto_id')
    //     ->withPivot('cantidad', 'precio', 'fecha_compra');
    //     }
    public function compraProducto(){
         return $this->hasMany(CompraProducto::class);
    }



}
