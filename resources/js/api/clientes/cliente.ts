import { UsuarioCompleto, UsuarioTable } from '@/types/usuario';
import {
    administracionErrores,
    ErrorHttp422Validation,
    ResponseError
} from '@/api/administracionErrores/administracionErrores';
import { AppError } from '@/types/erroresExcepciones';
import { ClienteCompleto, ClienteTable } from '@/types/cliente';
import { ComboBoxItem } from '@/types';

export async function ObtenerClientes(): Promise<ClienteTable[]> {
    // 1. Obtener los usuarios y retornarlos

    const response = await fetch(route('usuario.cliente.consulta.json'), {
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

        await administracionErrores(errorResponse, 'Error al consultar clientes', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al consultar clientes', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    return data.clientes;
}

export async function RegistrarCliente(cliente: ClienteCompleto): Promise<ClienteCompleto>{
    // 1. Enviar la data del usuario como un PUT

    const response = await fetch(route('usuario.cliente.registro.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(cliente),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...cliente, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al registrar cliente", false);
        throw new AppError(errorResponse.errorApp , response.status, "Error al registrar cliente", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    cliente.id = data.idCliente;
    return { ...cliente, errores: [] };

}

export async function ActualizarCliente(cliente: ClienteCompleto): Promise<ClienteCompleto>{
    // 1. Enviar la data del usuario como un PUT

    const response = await fetch(route('usuario.cliente.actualiza.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(cliente),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...cliente, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al actualizar cliente", false);
        throw new AppError(errorResponse.errorApp , response.status, "Error al actualizar cliente", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    await response.json();
    return { ...cliente, errores: [] };

}


export async function ObtenerCliente(idCliente: number): Promise<ClienteCompleto>{
    const response = await fetch(route('usuario.cliente.detalle.json', {id: idCliente}), {
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

        await administracionErrores(errorResponse, 'Error al consultar cliente', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al consultar cliente', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();

    return data.cliente;
}

export async function ObtenerClientesCombobox(): Promise<ComboBoxItem[]> {
    // 1. Obtener los usuarios y retornarlos

    const response = await fetch(route('usuario.cliente.consulta.json.combobox'), {
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

        await administracionErrores(errorResponse, 'Error al consultar clientes', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al consultar clientes', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    return data.clientes;
}
