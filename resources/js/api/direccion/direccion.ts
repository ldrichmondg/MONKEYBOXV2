import { administracionErrores } from '@/api/administracionErrores/administracionErrores';
import { Direccion } from '@/types/direccion';

export async function obtenerDirecciones(idCliente: number): Promise<Direccion[] | null> {
    const response = await fetch(route('direccion.consulta.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            idCliente: idCliente,
        }),
    });

    if (!response.ok) {
        await administracionErrores(response, 'Error al consultar las direcciones');
        throw new Error('No se cargaron las direcciones');
    }

    const data = await response.json();
    const direcciones: Direccion[] = data.direcciones;

    return direcciones;
}
