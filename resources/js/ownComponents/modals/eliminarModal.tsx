import Swal from 'sweetalert2'
import {ErrorModal} from '@/ownComponents/modals/errorModal';
import {ExitoModal} from '@/ownComponents/modals/exitoModal';

async function ContenidoModal(titulo: string, textoCuerpo: string): Promise<boolean> {
    return new Promise((resolve) => {
        Swal.fire({
            html: `
                <div class="w-[95%] p-4">
                    <div class="flex justify-center">
                        <svg class="lucide lucide-trash2-icon lucide-trash-2 text-red-400 size-20 " xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </div>
                    <h2 class="swal2-title py-3">${titulo}</h2>
                    <p class="swal2-html-container pt-4">${textoCuerpo}</p>
                </div>
               `,
            showCancelButton: true,
            confirmButtonColor: "#ED1B24FF",
            cancelButtonColor: "#1C75BCFF",
            confirmButtonText: "Eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => resolve(result.isConfirmed));
    });
}
export async function EliminarModal({ description = '', titulo = '', route = '' }: { description: string, titulo:string, route: string }) {

    const confirmarEliminar = await ContenidoModal(titulo,description);

    if (!confirmarEliminar) return;

    try{
        const result: Response = await fetch(route, {
            method: 'POST'
        })

        if (result.ok){
            ExitoModal('Éxito', 'Se eliminó exitosamente.');
        }else{
            throw new Error('Problema con el request: ' + result.json());
        }
    }
    catch(error) {
        ErrorModal('Error al eliminar','Ha habido un problema al eliminar. Por favor contactar a soporte técnico');
        console.log('[EM] error: ' + error);
    }


}


