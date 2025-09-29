<x-mail::message>
<h2 class="text-primary">Corrección de Notificación</h2>

Hola {{ $usuario->NOMBRE }},

Queremos informarte que el mensaje anterior que indicaba que tu paquete había llegado a Costa Rica fue enviado por error.

Te pedimos disculpas por cualquier confusión causada y te agradecemos por tu comprensión.  
Por favor, ignora ese mensaje, ya que no refleja el estado actual de tu envío.

Nuestro equipo está trabajando continuamente para mantenerte informado con datos precisos y actualizados.

Ante cualquier consulta, no dudes en comunicarte con nosotros.

Detalles del tracking:

<ul>
<li>ID del Tracking: {{ $tracking->IDTRACKING }}</li>
<li>Descripción: {{ $tracking->DESCRIPCION }}</li>
</ul>

Saludos cordiales,  
MonkeyBox

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>
</x-mail::message>
