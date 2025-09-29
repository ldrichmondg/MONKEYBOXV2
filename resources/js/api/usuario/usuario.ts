import { UsuarioCompleto, UsuarioTable } from '@/types/usuario';
import {
    administracionErrores,
    ErrorHttp422Validation,
    ResponseError
} from '@/api/administracionErrores/administracionErrores';
import { AppError } from '@/types/erroresExcepciones';

export async function ObtenerUsuarios(): Promise<UsuarioTable[]> {
    // 1. Obtener los usuarios y retornarlos

    const response = await fetch(route('usuario.usuario.consulta.json'), {
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

        await administracionErrores(errorResponse, 'Error al consultar usuarios', false);
        throw new AppError(errorResponse.errorApp, response.status, 'Error al consultar usuarios', errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    const data = await response.json();
    return data.usuarios;
}

export async function ActualizarUsuario(usuario: UsuarioCompleto): Promise<UsuarioCompleto>{
    // 1. Enviar la data del usuario como un PUT

    const response = await fetch(route('usuario.usuario.actualiza.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(usuario),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...usuario, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al eliminar la prealerta", false);
        throw new AppError(errorResponse.errorApp , response.status, "Error al eliminar la prealerta", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    await response.json();
    return { ...usuario, errores: [] };

}

export async function RegistrarUsuario(usuario: UsuarioCompleto): Promise<UsuarioCompleto>{
    // 1. Enviar la data del usuario como un PUT

    const response = await fetch(route('usuario.usuario.actualiza.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(usuario),
    });

    if (response.status === 422) {
        const errores = await ErrorHttp422Validation(response);
        return { ...usuario, errores };
    }

    if (!response.ok) {
        const errorResponse: ResponseError = await response.json(); //no retorna nada porque es actualizar, pero si retorna, son errores
        errorResponse.httpStatus = response.status;

        await administracionErrores(errorResponse, "Error al eliminar la prealerta", false);
        throw new AppError(errorResponse.errorApp , response.status, "Error al eliminar la prealerta", errorResponse.titleMessage);
    }

    // Si llegó acá es que t#do bien
    await response.json();
    return { ...usuario, errores: [] };

}
