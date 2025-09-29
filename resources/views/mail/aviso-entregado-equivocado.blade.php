<x-mail::message>
<h2 class="text-primary">Corrección de Notificación</h2>

Hola {{ $usuario->NOMBRE }},

Queremos informarte que el mensaje anterior indicando que tu paquete había sido entregado fue enviado por error.

Lamentamos la confusión que esto haya podido generar. Tu paquete aún no ha sido entregado, y te estaremos notificando oportunamente cuando eso ocurra.

Detalles del tracking:

<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
</ul>

Agradecemos tu comprensión y paciencia.  
Si tienes alguna duda o deseas obtener información actualizada sobre tu envío, no dudes en contactarnos.

Saludos cordiales,  
Equipo de MonkeyBox

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>
</x-mail::message>
