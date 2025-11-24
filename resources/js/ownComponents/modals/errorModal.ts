import Swal from 'sweetalert2'

export function ErrorModal(titulo = '', textoCuerpo = ''): void {
    Swal.fire({
        icon: "error",
        title: titulo,
        text: textoCuerpo,
        confirmButtonColor: "#3150F1",
        confirmButtonText: 'Aceptar',
        zIndex: 20000,             // 👈 lo pone arriba de todo
        heightAuto: false          // 👈 evita problemas dentro de modales
    })
}
