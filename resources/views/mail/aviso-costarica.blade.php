<x-mail::message>
<h2 class="text-primary">¡Tu paquete ya está en Costa Rica!</h2>

¡Hola {{ $usuario->NOMBRE }}!

Nos complace informarte que tu paquete ha llegado a Costa Rica.

Estamos procesando los últimos pasos para que lo recibas lo más pronto posible. 

Si necesitas más información o deseas coordinar la entrega, no dudes en contactarnos.

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
