
import { EstatusTable } from '@/types/table'

interface estatusMBOXResponse {
    DESCRIPCION: string,
    COLORCLASS: string
}

export async function obtenerEstatusMBox( estatus: string[] ): Promise<EstatusTable[] | null> {

    try{

        const response = await fetch(route('usuario.estadoMBox.detalles.json'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                estadosMBox: estatus
            })
        });

        if (!response.ok) {
            throw new Error(JSON.stringify(response.json()));
        }

        const data: estatusMBOXResponse[] = await response.json();
        const estatusMBox: EstatusTable[] = [];
        data.forEach(estado => {
            estatusMBox.push({
                descripcion: estado.DESCRIPCION,
                colorClass: estado.COLORCLASS,
            })
        })

        return estatusMBox;

    } catch (error) {
        console.log('[API->EstatusMBOX->obtenerEstatusMBox] Hubo un error al obtener los estados: ', error);
        return null;
    }
}
