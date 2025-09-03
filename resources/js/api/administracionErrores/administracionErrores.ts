import { ErrorModal } from '@/ownComponents/modals/errorModal';

interface ResponseError {
    message: string;
    status: string;
    titleMessage?: string;
    redirect?: string;
}
export async function administracionErrores(response: Response, errorMensajeTitle: string): Promise<boolean> {
    // 1. Obtener el status request
    // 2. Crear el objeto ResponseError
    // 3. Validar si es alguno de los errores ya registrados
    // 4. Retornar true si cayo en 401 o 419
    // 5. false si cae en otro

    // - Excepciones: Esta funcion no cubre error http 422 porque depende mucho de como se maneje

    // 1. Obtener el status request
    const statusRequest: number = response.status;

    // - Validar que no haya entrado un request correcto (200 hasta 299)
    if (statusRequest > 200 && statusRequest < 299) return false;

    // 2. Crear el objeto ResponseError
    const responseError: ResponseError = await response.json();

    // 3. Validar si es alguno de los errores ya registrados
    const resp401 = ErrorHttp401Unauthorized(responseError, statusRequest);
    if (resp401) return true;

    const resp419: boolean = ErrorHttp419CSRFTokenMismatch(responseError, statusRequest);
    if (resp419) return true;

    ErrorHttp500ServerError(responseError, statusRequest, errorMensajeTitle);

    return false;
}

function ErrorHttp419CSRFTokenMismatch(responseError: ResponseError, statusCode: number): boolean {
    // 1. Se redirige al login y se le manda el mensaje

    if(statusCode == 419){
        if (responseError.redirect)
            window.location.href = responseError.redirect;
        else { //por si no viene el redirect
            ErrorModal('Error Request 419 CSRF Token', 'No se obtuvo el campo redirect cuando deberia de tenerlo');
            window.location.href = '/';
        }
        return true;
    }

    return false;
}

function ErrorHttp401Unauthorized(responseError: ResponseError, statusCode: number): boolean {
    // 1. Se redirige al login y se le manda el mensaje

    if(statusCode == 401){
        if (responseError.redirect)
            window.location.href = responseError.redirect;
        else { //por si no viene el redirect
            ErrorModal('Error Request 401 Unauthorized', 'No se obtuvo el campo redirect cuando deberia de tenerlo');
            window.location.href = '/';
        }
        return true;
    }

    return false;

}

function ErrorHttp500ServerError(responseError: ResponseError, statusCode: number, errorMensajeTitle: string): void {
    // 1. Los errores 500 solo se envia un error modal para el usuario
    // - Seran usados los 500 como contexto para el usuario de algun error que pueden hacer algo en el momento
    // como volver a intentarlo, llenar de nuevo el form, etc
    // - Como retornan false, significa que no necesitan parar el flujo del request que tenga donde fue llamado su fn padre

    if(statusCode == 500){
        if (responseError.message)
            ErrorModal(errorMensajeTitle, responseError.message);
        else
            ErrorModal('Se ha producido un error', 'Se ha producido un error al procesar una actividad. Si esto se repite favor contactar al departamento de TI');
    }
}
