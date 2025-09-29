<x-mail::message>
<h2 class="text-primary">Actualización de tu Envío</h2>

Hola {{ $usuario->NOMBRE }},

Queremos informarte que tu paquete ha sido correctamente enlazado en nuestro sistema.

Detalles del tracking:

<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
</ul>

A partir de ahora podrás seguir el progreso de tu envío a través de tu cuenta.

Si tienes alguna pregunta o necesitas más información, no dudes en ponerte en contacto con nosotros.

¡Gracias por confiar en MonkeyBox!

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

Saludos cordiales,  
Equipo de MonkeyBox
</x-mail::message>
