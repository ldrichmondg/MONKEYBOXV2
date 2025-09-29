<x-mail::message>
<h2 class="text-primary">¡Feliz Cumpleaños {{ $usuario->NOMBRE }}! Disfruta un regalo especial de nuestra parte</h2>

¡Hoy es un día muy especial, y queremos celebrarlo contigo! 🎂

En MonkeyBox, sabemos que los cumpleaños son perfectos para recibir sorpresas, así que tenemos un regalo para ti:

✨ 20% de descuento en tu próximo envío ✨

Código de descuento: {{ $codigoDescuento }}
Válido hasta: {{ $fechaVencimiento }}
Aprovecha este detalle para traer ese paquete especial desde Amazon, Alibaba, o cualquier tienda en línea directamente a Costa Rica. Estamos aquí para hacer que tu experiencia sea aún más especial.

Gracias por confiar en nosotros para tus envíos. ¡Esperamos que tengas un cumpleaños increíble lleno de alegría y buenos momentos! 🎉🎈

Con cariño,
El equipo de MonkeyBox

<x-mail::button :url="url('/')">
    Ingresar
</x-mail::button>
</x-mail::message>
