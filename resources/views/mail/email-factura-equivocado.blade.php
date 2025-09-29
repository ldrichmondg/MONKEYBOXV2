<x-mail::message>
# <h2 class="text-danger">Disculpas por el error</h2>

Hola {{ $usuario->NOMBRE }},

Lamentamos informarte que, por un error involuntario de nuestro sistema, has recibido un correo de una factura que no estaba destinado a ti o contenía información incorrecta.


**Detalles del tracking:**

<ul>
<li><strong>ID del Tracking:</strong> {{ $tracking->IDTRACKING }}</li>
<li><strong>Descripción:</strong> {{ $tracking->DESCRIPCION }}</li>
</ul>

Queremos ofrecerte nuestras más sinceras disculpas por cualquier inconveniente que esto haya podido causarte.  
Estamos tomando las medidas necesarias para evitar que este tipo de situaciones vuelvan a ocurrir.

Si tienes alguna duda o necesitas más información, no dudes en contactarnos.

Agradecemos tu comprensión y confianza.

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>

Atentamente,
**Equipo de MonkeyBox**
</x-mail::message>
