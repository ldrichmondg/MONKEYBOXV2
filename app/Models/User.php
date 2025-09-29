<?php

namespace App\Models;

use App\Mail\RecoverPassword;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Model implements AuthenticatableContract, CanResetPassword
{
    use Authenticatable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['CEDULA', 'NOMBRE', 'APELLIDOS', 'email', 'TELEFONO', 'IDPERFIL', 'email_verified_at', 'password'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'IDPERFIL' => 'integer',
    ];

    protected $table = 'users';

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = config('app.url') . '/reset-password/' . $token;
        Mail::to($this->email)->send(new RecoverPassword($url));
    }

    public function perfil(): HasOne
    {
        return $this->hasOne(Perfil::class, 'id', 'IDPERFIL');
    }

    public function getNombrePerfil()
    {
        return Perfil::find($this->IDPERFIL)->DESCRIPCION;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function hasRole(string $perfilIndicado): bool
    {

        if ($perfilIndicado == null) {
            return false;
        }
        if ($this->IDPERFIL == null) {
            return false;
        }
        if ($this->perfil->DESCRIPCION == $perfilIndicado) {

            return true;
        }

        return false;
    }

    public function telefonoGuion(): string
    {

        // Get the length of the string
        $length = strlen($this->TELEFONO);

        // Calculate the position to insert the hyphen (middle position)
        $middle = (int) ($length / 2);

        // Split the string into two parts and insert the hyphen
        $firstPart = substr($this->TELEFONO, 0, $middle);
        $secondPart = substr($this->TELEFONO, $middle);

        // Concatenate the parts with a hyphen
        return $firstPart.'-'.$secondPart;
    }

    public function nombreCompleto(): string
    {
        $apellido = explode(' ', $this->APELLIDOS)[0];

        return $this->NOMBRE.' '.$apellido;
    }

    public function nombreCompletoDosApellidos(): string
    {
        return $this->NOMBRE.' '.$this->APELLIDOS;
    }

    public function primerLetraApellido(): string
    {
        return mb_substr($this->APELLIDOS, 0, 1, 'UTF-8').'.';
    }
}
