import Swal from 'sweetalert2'
async function ContenidoModal(titulo: string, textoCuerpo: string): Promise<boolean> {
    return new Promise((resolve) => {
        Swal.fire({
            html: `
                <div class="w-[95%] p-4">
                    <div class="flex justify-center">
                        <svg class="lucide lucide-circle-question-mark-icon lucide-circle-question-mark text-orange-400 size-25" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    </div>
                    <h2 class="swal2-title py-3">${titulo}</h2>
                    <p class="swal2-html-container pt-4">${textoCuerpo}</p>
                </div>
               `,
            showCancelButton: true,
            confirmButtonColor: "#ED1B24FF",
            cancelButtonColor: "#1C75BCFF",
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar",
        }).then((result) => resolve(result.isConfirmed));
    });
}
export async function SeguroModal({ description = '', titulo = ''}: { description: string, titulo:string }) {

    return await ContenidoModal(titulo,description);
}


