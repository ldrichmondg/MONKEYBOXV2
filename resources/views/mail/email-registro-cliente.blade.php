<x-mail::message>
# Registro a sistema de Tracking MonkeyBox

Estimado/a {{$nombre}}.

¡Bienvenido al sistema MonkeyBox!  
Estamos encantados de ayudarte con tus paquetes. 
Te hemos creado un usuario para que puedas ver toda la trazabilidad de tus paquetes en nuestro sistema 
en el siguiente enlace: <a href="http://127.0.0.1:8000/">Link al Sistema</a>.

Tus credenciales son las siguientes:
<ul>
<li>Correo: {{$correo}}</li>
<li>Contraseña: {{$password}}</li>
<li>Casillero: {{$casillero}}</li>
</ul>


Este registro es esencial para asegurar que tu experiencia con nosotros sea fluida y sin inconvenientes.

Gracias por confiar en nosotros. ¡Esperamos verte pronto!

Saludos cordiales,

MonkeyBox

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

</x-mail::message>
