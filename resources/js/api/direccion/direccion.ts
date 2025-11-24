import {
    administracionErrores,
    ErrorHttp422Validation,
    ResponseError
} from '@/api/administracionErrores/administracionErrores';
import { Direccion } from '@/types/direccion';
import { ComboBoxItem } from '@/types';
import { AppError } from '@/types/erroresExcepciones';
import { TrackingTable } from '@/types/tracking';

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

export async function obtenerTiposDirecciones(): Promise<ComboBoxItem[]>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.tiposdirecciones.consulta.json', ), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json();
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener los cantones", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener los cantones", errorResponse.titleMessage);
    }
    const data = await response.json();
    return data.tiposDirecciones;
}


export async function obtenerProvincias(): Promise<ComboBoxItem[]>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.provincias.consulta.json', ), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json();
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener los cantones", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener los cantones", errorResponse.titleMessage);
    }
    const data = await response.json();
    return data.provincias;
}

export async function obtenerCantones(idProvincia: number): Promise<ComboBoxItem[]>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.cantones.consulta.json', { id: idProvincia }), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener los cantones", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener los cantones", errorResponse.titleMessage);
    }
    const data = await response.json();
    return data.cantones;
}

export async function obtenerDistritos(idCanton: number): Promise<ComboBoxItem[]>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.distrito.consulta.json', { id: idCanton }), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener los distritos", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener los distrito", errorResponse.titleMessage);
    }
    const data = await response.json();
    return data.distritos;
}

export async function obtenerCodigoPostal(idDistrito: number): Promise<number>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.distrito.consulta.codigopostal.json', { id: idDistrito }), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener el código postal", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener el código postal", errorResponse.titleMessage);
    }
    const data = await response.json();
    return data.codigoPostal;
}

export async function obtenerProvinciaCantonDistrito(codigoPostal: number): Promise<number>{
    // en el caso de que haya un error se tira AppError y el error que tira se le pone en el campo cantones
    const response = await fetch(route('usuario.codigopostal.detalle.json', { codigopostal: codigoPostal }), {
        method: 'GET'
    });


    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al obtener la información del código postal", false);
        throw new AppError(errorResponse.errorApp ,response.status, "Error al obtener la información del código postal", errorResponse.titleMessage);
    }

    return await response.json();
}

export async function obtenerTrackings(idDireccion: number): Promise<Direccion[] | null> {
    const response = await fetch(route('usuario.direccion.consulta.json.trackings'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            idDireccion: idDireccion,
        }),
    });

    if (!response.ok) {
        await administracionErrores(response, 'Error al consultar los trackings de la dirección solicitada');
        throw new AppError(errorResponse.errorApp ,response.status, "Error al consultar los trackings de la dirección solicitada", errorResponse.titleMessage);
    }

    const data = await response.json();
    const trackings: TrackingTable[] = data.trackingsDireccion;

    return trackings;
}
