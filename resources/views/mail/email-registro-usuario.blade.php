<x-mail::message>
# Registro a sistema de Tracking MonkeyBox

Estimado/a {{$nombre}}.

¡Bienvenido al sistema MonkeyBox!
Estamos encantados de tenerte como parte de nuestro equipo.
Para comenzar, te invitamos a registrarte en nuestro sistema
de administración de trackings en el siguiente enlace: <a href="{{ config('app.url') }}">Link al Sistema</a>.

Tus credenciales son las siguientes:
<ul>
<li>Correo: {{$correo}}</li>
<li>Contraseña: {{$password}}</li>
</ul>


Este registro es esencial para asegurar que tu experiencia con nosotros sea fluida y sin inconvenientes.

Gracias por confiar en nosotros. ¡Esperamos verte pronto!

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

Saludos cordiales,

MonkeyBox
</x-mail::message>
