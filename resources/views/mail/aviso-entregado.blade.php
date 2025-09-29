<x-mail::message>
<h2 class="text-primary">¡Tu paquete ha sido entregado!</h2>

¡Hola {{ $usuario->NOMBRE }}!

Nos complace informarte que tu paquete ha sido entregado con éxito.

Si tienes alguna consulta o necesitas asistencia adicional, no dudes en contactarnos.

Detalles del tracking:

<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
</ul>

¡Gracias por confiar en nosotros!

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>
</x-mail::message>
