import { administracionErrores, ErrorHttp422Validation, ResponseError } from '@/api/administracionErrores/administracionErrores';
import { AppError } from '@/types/erroresExcepciones';
import { TrackingCompleto } from '@/types/tracking';

export async function ActualizarTracking(tracking: TrackingCompleto): Promise<TrackingCompleto> {
    // 1. Se ejecuta el request

    const formData: FormData = FormDataTracking(tracking);

    const response = await fetch(route('usuario.tracking.actualiza.json'), {
        method: 'POST',
        headers: {
            Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...tracking, errores };
    }

    // Otros errores HTTP
    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al actualizar tracking', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al actualizar tracking', errorResponse.titleMessage);
    }

    await response.json(); //no necesito nada
    return { ...tracking, errores: [] };
}

export async function ActualizarEstado(tracking: TrackingCompleto): Promise<TrackingCompleto> {
    // 1. Funcion es para ingresar el estado que se desea actualizar/cambiar. el idProveedor es para saber si se debe de
    const response = await fetch(route('usuario.tracking.json.cambioestado'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            id: tracking.id,
            ordenEstado: tracking.ordenEstatus,
            idProveedor: tracking.idProveedor,
        }),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...tracking, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al actualizar estado de tracking', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al actualizar estado de tracking', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    const trackingRespuesta: TrackingCompleto = data.trackingActualizado;
    return { ...trackingRespuesta, errores: [] };
}

export async function SubirFactura(tracking: TrackingCompleto): Promise<TrackingCompleto> {
    const formData = new FormData();
    formData.append('factura', tracking.factura);
    formData.append('id', tracking.id);

    const response = await fetch(route('usuario.tracking.actualiza.json.subirfactura'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...tracking, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al insertar factura al tracking', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al insertar factura al tracking', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    const trackingRespuesta: TrackingCompleto = data.trackingActualizado;
    return { ...trackingRespuesta, errores: [] };
}

export async function EliminarFactura(tracking: TrackingCompleto): Promise<TrackingCompleto> {
    const formData = new FormData();
    formData.append('id', tracking.id);

    const response = await fetch(route('usuario.tracking.actualiza.json.eliminarfactura'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...tracking, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al insertar factura al tracking', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al insertar factura al tracking', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    const trackingRespuesta: TrackingCompleto = data.trackingActualizado;
    return { ...trackingRespuesta, errores: [] };
}

export async function SincronizarCambios(tracking: TrackingCompleto): Promise<TrackingCompleto> {
    // para sincronizar los cambios del tracking que tengo actualmente con los nuevos cambios de ParcelsApp o de Aeropost
    const formData: FormData = FormDataTracking(tracking);

    const response = await fetch(route('usuario.tracking.actualiza.json.sincronizar'), {
        method: 'POST',
        headers: {
            Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...tracking, errores };
    }

    // Otros errores HTTP
    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, 'Error al actualizar tracking', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al actualizar tracking', errorResponse.titleMessage);
    }

    const data = await response.json(); //no necesito nada
    return { ...data.tracking, errores: [] };

}

function FormDataTracking(tracking: TrackingCompleto): FormData {
    // Es la construccion del tracking completo para poder actualizarlo completamente

    const formData = new FormData();

    // Convierte los campos normales
    Object.entries(tracking).forEach(([key, value]) => {
        // Si es un objeto, conviértelo a JSON excepto las imágenes
        if (typeof value === 'object' && key !== 'imagenes' && key !== 'historialesTracking' && value !== null) {
            formData.append(key, JSON.stringify(value));
        } else if (typeof value !== 'undefined' && key !== 'imagenes' && key !== 'historialesTracking') {
            formData.append(key, value as any);
        }
    });

    //agrega los historialesTracking
    // Agrega los historialesTracking
    tracking.historialesTracking.forEach((hist: any, index: number) => {
        Object.entries(hist).forEach(([k, v]) => {
            if (k === 'actions') return; // opcional: ignorar actions

            // Si es boolean, convertir a 0 o 1
            if (typeof v === 'boolean') {
                formData.append(`historialesTracking[${index}][${k}]`, v ? '1' : '0');
            } else {
                formData.append(`historialesTracking[${index}][${k}]`, v as any);
            }
        });
    });

    // Agrega las imágenes (array)
    tracking.imagenes.forEach((img, index) => {
        formData.append(`imagenes[${index}][archivo]`, img.archivo);
        formData.append(`imagenes[${index}][tipoImagen]`, String(img.tipoImagen));
        if (img.id) formData.append(`imagenes[${index}][id]`, String(img.id));
    });

    return formData;
}
