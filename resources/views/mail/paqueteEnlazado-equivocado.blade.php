<x-mail::message>
<h2 class="text-primary">Disculpas por el Correo Anterior</h2>

Hola {{ $usuario->NOMBRE }},

Queremos disculparnos por el correo anterior referente al enlace de un pedido a tu nombre, ya que fue enviado por error.

Por favor, ignora ese mensaje, ya que no corresponde a un proceso real relacionado con tu cuenta.  
Estamos tomando medidas para evitar que este tipo de situaciones se repitan.

Si tienes alguna duda o recibiste información que te genera confusión, no dudes en ponerte en contacto con nosotros. Estamos aquí para asistirte.

Detalles del tracking:

<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
</ul>
Gracias por tu comprensión y por seguir confiando en MonkeyBox.

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

Saludos cordiales,  
Equipo de MonkeyBox
</x-mail::message>
