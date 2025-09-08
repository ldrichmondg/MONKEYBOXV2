import { ComboBoxItem } from '@/types';
import { obtenerDirecciones } from '@/api/direccion/direccion';
import { Direccion } from '@/types/direccion';

/*
 **Throws obtenerDirecciones
*/

export async function comboboxDirecciones(idCliente: number): ComboBoxItem[] {
    const direcciones: Direccion[] = await obtenerDirecciones(idCliente);
    const direccionesItems: ComboBoxItem[] = [];

    for( const direccion of direcciones ) {
        const direccionItem: ComboBoxItem = {
            id: direccion.id,
            descripcion: direccion.paisEstado + ', '+ direccion.direccion
        }

        direccionesItems.push(direccionItem);
    }

    return direccionesItems;
}
