import { PrealertaActualizar } from '@/types/prealerta';
import {
    administracionErrores,
    ErrorHttp422Validation,
    ResponseError
} from '@/api/administracionErrores/administracionErrores';
import { AppError } from '@/types/erroresExcepciones';

export async function ActualizarPrealerta(prealerta: PrealertaActualizar): Promise<PrealertaActualizar>{
    // 1. Se van a actualizar los datos de la prealerta
    // 2. Retornar los errores

    const response = await fetch(route('usuario.prealerta.actualiza.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            descripcion: prealerta.descripcion,
            valor: prealerta.valor,
            idTracking: prealerta.idTracking,
            idProveedor: prealerta.idProveedor,
        }),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...prealerta, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al actualizar la prealerta", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al actualizar la prealerta", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    await response.json();
    return { ...prealerta, errores: [] };
}

export async function EliminarPrealertaPorTracking(numeroTracking: string): Promise<void>{
    // 1. Hacer el request donde se envia la solicitud

    const response = await fetch(route('usuario.prealerta.eliminar.json'), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            idTracking: numeroTracking
        }),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...prealerta, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al eliminar la prealerta", false);
        throw new AppError(errorResponse.errorApp , response.status, "Error al eliminar la prealerta", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    await response.json();
}
