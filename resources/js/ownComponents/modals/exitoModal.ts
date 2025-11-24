import Swal from 'sweetalert2'

export function ExitoModal(titulo = '', textoCuerpo = ''): void {
    Swal.fire({
        title: titulo,
        icon: "success",
        text: textoCuerpo,
        confirmButtonColor: "#3150F1",
        confirmButtonText: 'Aceptar',
        zIndex: 20000
    });
}
