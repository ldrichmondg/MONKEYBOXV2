<x-mail::message>
<h2 class="text-primary">Tracking Eliminado</h2>

Hola {{ $usuario->NOMBRE }},

Te informamos que el tracking con los siguientes detalles ha sido eliminado:
<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
<li>Desde: {{ $tracking->DESDE }}</li>
<li>Hasta: {{ $tracking->HASTA }}</li>
<li>Destino: {{ $tracking->DESTINO }}</li>
</ul>

Si tienes alguna duda o necesitas más información, no dudes en contactarnos.

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

</x-mail::message>
