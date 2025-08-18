import { Proveedor } from '@/types/proveedor';
import { ComboBoxItem } from '@/types';

export async function obtenerProveedores(): Promise<Proveedor[]> | null {

    try{ //quede en hacer una URL para saber los proveedores

        const response = await fetch(route('usuario.proveedor.consultar.json'));

        if (!response.ok) {
            throw new Error(response.json());
        }

        const data = await response.json();
        const proveedores: Proveedor[] = [];
        data.forEach(proveedor => {
            proveedores.push({
                id: proveedor.id,
                nombre: proveedor.NOMBRE,
            })
        })

        return proveedores;

    } catch (error) {
        console.log('[API->proveedor->obtenerProveedores] Hubo un error al obtener los proveedores: ', error);
        return null;
    }
}

export async function comboboxProveedor(): ComboBoxItem[] {

    // - Retornar los proveedores pero como tipo comoboboxItem
    const proveedoresItems: ComboBoxItem[] = [];

    try{

        const proveedores: Proveedor[] = await obtenerProveedores();
        if(!proveedores){
            throw new Error('No se cargaron los proveedores, vino null');
        }

        proveedores.forEach( proveedor => {
            proveedoresItems.push({
                id: proveedor.id,
                descripcion: proveedor.nombre
            })
        })

        if(proveedoresItems.length == 0){
            return [];
        }

        return proveedoresItems;

    } catch (error) {
        console.log('[API->proveedor->comboboxProveedor] Hubo un error al obtener los proveedores: ', error);
        return proveedoresItems;
    }
}
