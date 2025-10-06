import { TrackingConPrealertaBaseProveedor } from '@/types/tracking';
import { ErroresInputs, parseLaravelValidationErrors } from '@/types/input';
import { administracionErrores } from '@/api/administracionErrores/administracionErrores';
import { PrealertaBase } from '@/types/prealerta';
import { InputError } from '@/types/input';

export async function PrealertarTracking(tracking: TrackingConPrealertaBaseProveedor ): Promise<TrackingConPrealertaBaseProveedor | null> {
    // 1. Se ejecuta el request
    // 2. Retorna un TrackingConPrealertaBaseProveedor, sino null

    try {
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

        // Otros errores HTTP
        if (!response.ok) {
            // Pasar response a tu función de administración de errores
            await administracionErrores(response, 'Error al crear la prealerta');
            throw new Error('Error al crear la prealerta');
        }

        const data: TrackingConPrealertaBaseProveedor = await response.json();
        data.errores = []; //como no hay errores, ponerlo vacio

        return data;

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        return null;
    }
}


/**
 *
 * @param idTracking
 * @param prealerta
 * @returns Promise<PrealertaBase & ErroresInputs>
 *     @throws Error
 */
export async function ActualizarPrealerta(idTracking: number, idProveedor: number, prealerta: PrealertaBase): Promise<PrealertaBase & ErroresInputs> {
    // 1. Llenar el request
    // 2. Retornar la prealerta si t#do sale bien
    // 3. Retornar los errores si algo sale mal

    const response = await fetch(route('usuario.prealerta.registro.json'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            idTracking: idTracking,
            valor: prealerta.valor,
            descripcion: prealerta.descripcion,
            idProveedor: idP,
        }),
    });


}
