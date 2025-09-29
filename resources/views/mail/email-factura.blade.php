<x-mail::message>
# <h2 class="text-primary">Factura enviada correctamente</h2>

Hola {{ $usuario->NOMBRE }},

Te informamos que tu factura ha sido generada y enviada exitosamente.

**Detalles del tracking:**

<ul>
<li><strong>ID del Tracking:</strong> {{ $tracking->IDTRACKING }}</li>
<li><strong>Descripción:</strong> {{ $tracking->DESCRIPCION }}</li>
</ul>

Puedes encontrar la factura adjunta a este correo.

Si tienes alguna duda o necesitas asistencia adicional, estamos a tu disposición.

Gracias por confiar en MonkeyBox.

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

Saludos cordiales,  
Equipo de MonkeyBox
</x-mail::message>
