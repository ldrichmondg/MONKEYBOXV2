import { TrackingTable } from '@/types/tracking';
import { administracionErrores, ResponseError } from '@/api/administracionErrores/administracionErrores';
import { AppError } from '@/types/erroresExcepciones';


export async function ObtenerTrackingsConsultadosTable(): Promise<TrackingTable[]>{
    // 1. Llamar al request donde obtengo todos los trackings y sus acciones
    const response = await fetch(route('usuario.tracking.consulta.json'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    });

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al consultar trackings', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al consultar trackings', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    return data.trackings;
}
