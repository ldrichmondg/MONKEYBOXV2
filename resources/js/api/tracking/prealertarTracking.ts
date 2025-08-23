import { TrackingConPrealertaBaseProveedor } from '@/types/tracking';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { parseLaravelValidationErrors } from '@/types/input';

export async function PrealertarTracking(tracking : TrackingConPrealertaBaseProveedor): TrackingConPrealertaBaseProveedor | null {
    // 1. Se ejecuta el request
    // 2. Retorna un TrackingConPrealertaBaseProveedor, sino null

    try{

        const response = await fetch(route('usuario.prealerta.registro.json'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                idTracking: tracking.idTracking,
                valor: tracking.prealerta.valor,
                descripcion: tracking.prealerta.descripcion,
                idProveedor: tracking.idProveedor,
            }),
        });

        if (response.status === 422) {
            // error validación Laravel
            const data = await response.json();
            const erroresParseados = parseLaravelValidationErrors(data);
            tracking.errores = erroresParseados;

            return tracking; // devuelves tracking con errores
        }

        if (response.status === 419) {
            // CSRF token expirado
            ErrorModal('Sesión expirada', 'Tu sesión ha expirado, por favor recarga la página.');
            return null;
        }

        if (response.status === 500) {
            ErrorModal('Error al registrar la prealerta', 'Hubo un error al registrar la prealerta');
            return null;
        }

        const data: TrackingConPrealertaBaseProveedor  = await response.json();
        data.errores = []; //como no hay errores, ponerlo vacio

        return data;

    } catch (e) {
        console.log('[API->PreealertarTracking->PT] error: ' + e);
        return null
    }
}
