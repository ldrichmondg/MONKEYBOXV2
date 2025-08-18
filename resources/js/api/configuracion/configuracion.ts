
import { Configuracion } from '@/types/configuracion';

export async function obtenerConfiguracion(): Promise<Configuracion | null> {
    try{
        const response = await fetch(route('usuario.configuracion.consultar.json'));

        if (!response.ok) {
            throw new Error('Error al obtener configuración');
        }

        const data = await response.json();

        const configuracion: Configuracion = {
            valor: data.valorDefaultPreAlerta,
        }

        return configuracion;

    } catch (error) {
        console.error('[API->Configuracion->configuracion] Hubo un error al obtener la config: ' + error);
        return null;
    }
}

