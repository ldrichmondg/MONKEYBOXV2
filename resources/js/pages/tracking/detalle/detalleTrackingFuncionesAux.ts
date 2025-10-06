/*
 Archivo donde se encuentra toda la logica de las acciones de detalle tracking
 */

import { SeguroModal } from '@/ownComponents/modals/seguroModal';
import { toast } from 'sonner';
import { TrackingCompleto, TrackingConPrealertaBaseProveedor } from '@/types/tracking';
import { PrealertarTracking } from '@/api/tracking/prealertarTracking';
import React from 'react';
import { PrealertaActualizar } from '@/types/prealerta';
import { ActualizarPrealerta, EliminarPrealertaPorTracking } from '@/api/prealerta/prealerta';
import { EstadoMBox } from '@/types/estadoMBox';
import { AppError } from '@/types/erroresExcepciones';
import { ActualizarEstado, EliminarFactura } from '@/api/tracking/detalleTracking';
import { ErrorModal } from '@/ownComponents/modals/errorModal';


export async function EstadoSiguienteAccionPrealertar(tracking: TrackingCompleto, setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, ){
    // # El estado siguiente consiste en que el tracking esta en el SPR y va a pasar a PDO
    // 1. Crear la prealerta
    const respuestaAceptada = await SeguroModal({
        titulo: 'Prealertar Tracking',
        description: '¿Está seguro de prealertar el tracking?'
    });

    if (!respuestaAceptada) return;

    const idToastPrealertar = toast.loading('Nuevo evento ejecutado', {
        description: 'Realizando Prealerta...'
    });

    //solo pongo los datos que ocupo
    const objetoTrackingPrealerta: TrackingConPrealertaBaseProveedor = {
        id: tracking.id,
        idTracking: tracking.idTracking,
        nombreCliente: '',
        descripcion: '',
        desde: '',
        hasta: '',
        destino: '',
        couriers: '',
        proveedor: '',
        idProveedor: tracking.idProveedor,
        trackingProveedor: '',
        prealerta: {
            id: -1,
            descripcion: tracking.descripcion,
            valor: tracking.valorPrealerta,
            idTrackingProveedor: -1,
        },
        errores: [],
        estatus: {
            descripcion: '',
            colorClass: ''
        },
        actions: [],
    };
    const trackingPrealertado: TrackingConPrealertaBaseProveedor | null = await PrealertarTracking(objetoTrackingPrealerta);

    if(!trackingPrealertado) {
        toast.error('Error al crear la prealerta', { id: idToastPrealertar, description: 'Hubo un error al crear la prealerta. Intentalo de nuevo o contacta al soporte TI.' });
        return;
    }

    setTracking( (prev) => ({
        ...prev,
        errores: trackingPrealertado.errores
    }))

    if(trackingPrealertado.errores.length > 0) {
        toast.error('Error al crear la prealerta', { id: idToastPrealertar, description: 'Hubo un error al crear la prealerta. Intentalo de nuevo o contacta al soporte TI.' });
    }else {
        toast.success("Prealerta completada con éxito!", { id: idToastPrealertar, description: 'Se realizó la prealerta con éxito.' });
        setTracking( (prev) => ({
            ...prev,
            estatus: trackingPrealertado.estatus.descripcion,
            estadoSincronizado: trackingPrealertado.estatus.descripcion,
            ordenEstatus: 2,
            ordenEstatusSincronizado: 2
        }))
    }
}

export async function EstadoAnteriorAccionPrealertar(tracking: TrackingCompleto, setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>> ) {
    // # Estado anterior se entiende como que pasa de RMI a PDO
    // # Que se debe hacer resumidamente: Actualizar la prealerta
    // # Ya los campos de descripcion, valor y proveedor están llenos para entrar a esta función

    const respuestaAceptada = await SeguroModal({
        titulo: 'Cambiar estado: RMI -> PDO ',
        description: '¿Está seguro de cambiar el estado de Retiro Miami a Prealertado?'
    });

    if (!respuestaAceptada) return;

    await ActualizarPrealertaAux(tracking, setTracking)
}

export async function EstadoActualAccionPrealertar(tracking: TrackingCompleto, setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>> ) {
    // # Estado actual se entiende como que se mantiene en PDO y solo actualiza la prealerta
    // # Que se debe hacer resumidamente: Actualizar la prealerta
    // # Ya los campos de descripcion, valor y proveedor están llenos para entrar a esta función

    const respuestaAceptada = await SeguroModal({
        titulo: 'Mantener estado: PDO',
        description: '¿Está seguro de actualizar la prealerta? Si cambió de proveedor, la prealerta anterior se elimina. '
    });

    if (!respuestaAceptada) return;

    await ActualizarPrealertaAux(tracking, setTracking)
}


async function ActualizarPrealertaAux(tracking: TrackingCompleto, setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>> ) {
    // 1. Mostrar el toast donde se menciona la actualizacion de la prealerta
    // 2. Enviar request de actualizar prealerta
    // 3. Enviar mensaje al usuario de exito/error de la actualizacion

    const idToastPrealertar = toast.loading('Nuevo evento ejecutado', {
        description: 'Actualizando Prealerta...'
    });

    const prealerta: PrealertaActualizar = {
        id: -1,
        descripcion: tracking.descripcion,
        valor: tracking.valorPrealerta,
        idTrackingProveedor: -1,
        idTracking: tracking.idTracking,
        idProveedor: tracking.idProveedor,
        errores: []
    }

    try {
        const prealertaRespuesta = await ActualizarPrealerta(prealerta);

        setTracking((prev) => ({
            ...prev,
            errores: prealertaRespuesta.errores //no vale la pena actualizar los datos porque son los mismos, solo errores cambia
        }))

        if (prealertaRespuesta.errores.length > 0) {
            toast.error('Error al crear la prealerta', {
                id: idToastPrealertar,
                description: 'Hubo un error al actualizar la prealerta. Intentalo de nuevo o contacta al soporte TI.'
            });
        } else {
            toast.success("Prealerta actualizada con éxito!", {
                id: idToastPrealertar,
                description: 'Se realizó la prealerta con éxito.'
            });
            setTracking((prev) => ({
                ...prev,
                estatus: 'Prealertado',
                estadoSincronizado: 'Prealertado',
                ordenEstatus: EstadoMBox.PREALERTADO,
                ordenEstatusSincronizado: EstadoMBox.PREALERTADO
            }))
        }

    } catch (e: any) {
        let textoToast: string = 'Hubo un error al actualizar la prealerta';
        console.log('PRINT 3')

        if (e instanceof AppError) {
            console.log('PRINT 4')
            switch (e.appCode){
                case 'ERROR_AEROPOST':
                    textoToast = 'Error de comunicación con Aeropost. Intentalo de nuevo o contacta a TI.';
                    break;
                case 'ERROR_INTERNO':
                    textoToast = 'Error al actualizar la prealerta. Intentalo de nuevo o contacta a TI.';
                    break;
                case 'PREALERTA_NOT_FOUND':
                    textoToast = 'No se encontró la prealerta.'
                    break;
            }

            toast.error('Error al actualizar prealerta', {
                id: idToastPrealertar,
                description: textoToast
            });
        }
    }
}


export async function AccionPreartar(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, ordenEstadoPresionado: number) {
    // 1. Validar que los campos de descripcion, valor y proveedor esten llenos

    // 2. Ver el estado actual si es anterior, siguiente o actual.
    // 2.1. Si es siguiente, prealertar
    // 2.2. Si es anterior, actualizar prealerta
    // 2.3. Si es el mismo, actualizar prealerta

    let camposLlenos: boolean = true;
    setTracking((prev) => ({
        ...prev,
        errores: []
    }));

    // 1. Validar que los campos de descripcion, valor y proveedor esten llenos
    if (tracking.descripcion == '') {
        camposLlenos = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'descripcion', message: 'La descripción es obligatoria' }
                ]
        }));
    }

    if (tracking.valorPrealerta === null || tracking.valorPrealerta <= 0) {
        camposLlenos = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'valorPrealerta', message: 'El valor es obligatorio' }
                ]
        }));
    }

    if (tracking.idProveedor == -1 || tracking.idProveedor == null) {
        camposLlenos = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'idProveedor', message: 'El proveedor es obligatorio' }
                ]
        }));
    }

    if (!camposLlenos) return;
    // 2. Ver el estado actual si es anterior, siguiente o actual.
    const estadoSiguiente: boolean = ordenEstadoPresionado - tracking.ordenEstatus === 1; // de SPR a PDO
    const estadoAnterior: boolean = ordenEstadoPresionado - tracking.ordenEstatus === -1; // de RMI a PDO
    const estadoActual: boolean = ordenEstadoPresionado === 2; //

    if (estadoSiguiente) {
        await EstadoSiguienteAccionPrealertar(tracking, setTracking);

    } else if (estadoAnterior) {
        await EstadoAnteriorAccionPrealertar(tracking, setTracking);

    } else if (estadoActual) {
        await EstadoActualAccionPrealertar(tracking, setTracking);
    } else {
        console.log('No es ninguno');
    }
}


// Si presiona el btn SPR
export async function AccionSinPrealertar(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, ordenEstadoPresionado: number, setMostrarDialogo: React.Dispatch<React.SetStateAction<boolean>>, setMensajeDialogo: React.Dispatch<React.SetStateAction<MensajeDialog>>) {
    // 1. Verificar que los campos de idProveedor, descripcion y valor estén vacíos
    // 2. Solo funcionará si pasa de PDO -> SPR, osea estadoAnterior
    // 3. Consultarle al usuario si desea eliminar la prealerta.
    // 4. Elimina la prealerta
    // 5. Poner estados en Sin Prealertar

    let camposVacios: boolean = true;
    setTracking((prev) => ({
        ...prev,
        errores: []
    }));

    // 1. Verificar que los campos de idProveedor, descripcion y valor estén vacíos
    if (tracking.descripcion != '') {
        camposVacios = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'descripcion', message: 'La descripción debe estar vacía' }
                ]
        }));
    }

    if (tracking.valorPrealerta !== null) {
        camposVacios = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'valorPrealerta', message: 'El valor debe estar vacío' }
                ]
        }));
    }

    if (tracking.idProveedor != -1) {
        camposVacios = false;
        setTracking((prev) => ({
            ...prev,
            errores:
                [...prev.errores,
                    { name: 'idProveedor', message: 'El proveedor debe estar vacío' }
                ]
        }));
    }

    if (!camposVacios) return;

    // 2. Solo funcionará si pasa de PDO -> SPR, osea estadoAnterior
    const estadoAnterior: boolean = ordenEstadoPresionado - tracking.ordenEstatus == -1;

    if(!estadoAnterior) return;

    // 3. Consultarle al usuario si desea eliminar la prealerta.
    const respuestaAceptada = await SeguroModal({
        titulo: 'Cambiar estado: PDO -> SPR',
        description: '¿Está seguro de eliminar la prealerta?'
    });

    if (!respuestaAceptada) return;

    const idToastEliminarPrealerta = toast.loading('Nuevo evento ejecutado', {
        description: 'Eliminando Prealerta...'
    });

    try {
        // 4. Elimina la prealerta
        await EliminarPrealertaPorTracking(tracking.idTracking);

        toast.success("Prealerta eliminada con éxito!", {
            id: idToastEliminarPrealerta,
            description: 'Se eliminó la prealerta con éxito.'
        });

        // 5. Poner estados en Sin Prealertar y trackingProveedor limpiarlo
        setTracking((prev) => ({
            ...prev,
            estatus: 'Sin Prealertar',
            ordenEstatus: 1,
            ordenEstatusSincronizado: 1,
            trackingProveedor: ''
        }));

    } catch (e: any) {

        if (e instanceof AppError) {
            toast.error('Error al eliminar la prealerta', {
                id: idToastEliminarPrealerta,
                description: 'Hubo un error al eliminar la prealerta. Intentalo de nuevo o contacta al soporte TI.'
            });
        }
    }
}


// Si presiona el btn RMI
export async function AccionRMI(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Cuando tocan el boton RMI no importa si viene de PDO o de TCR
    // 2. Validar que tenga el proveedor
    // 2.1. Lo que importa es si el proveedor es MiLocker o Aeropost
    // Si es ML, entonces poner tanto en status como statusSincronizado el nuevo estado
    // Si es AP, poner solo en status

    try {
        if (tracking.idProveedor == -1) {
            setTracking((prev) => ({
                ...prev,
                errores: [
                    {name: 'idProveedor', message: 'Debe seleccionar un proveedor.'}
                ]
            }));
        } else if (tracking.idProveedor == 1 || tracking.idProveedor == 2) { //proveedor = Aeropost
            setActualizando(true);
            const trackingParametro: TrackingCompleto = {
                ...tracking,
                ordenEstatus: 3,
            }
            setTracking(await ActualizarEstado(trackingParametro));
        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }

}

//Si presiona el btn TCR
export async function AccionTCR(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Cuando tocan el boton TCR no importa si viene de RMI o de TCR
    // 2. Validar que tenga el proveedor
    // 2.1. Lo que importa es si el proveedor es MiLocker o Aeropost
    // Si es ML, entonces poner tanto en status como statusSincronizado el nuevo estado
    // Si es AP, poner solo en status

    try {
        if (tracking.idProveedor == -1) {
            setTracking((prev) => ({
                ...prev,
                errores: [
                    {name: 'idProveedor', message: 'Debe seleccionar un proveedor.'}
                ]
            }));
        } else if (tracking.idProveedor == 1 || tracking.idProveedor == 2) { //proveedor = Aeropost
            setActualizando(true);
            const trackingParametro: TrackingCompleto = {
                ...tracking,
                ordenEstatus: 4,
            }
            setTracking(await ActualizarEstado(trackingParametro));
        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }

}

//Si presiona el btn PA
export async function AccionPA(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Cuando tocan el boton PA no importa si viene de TCR o de OMB
    // 2. Validar que tenga el proveedor
    // 2.1. Lo que importa es si el proveedor es MiLocker o Aeropost
    // Si es ML, entonces poner tanto en status como statusSincronizado el nuevo estado
    // Si es AP, poner solo en status

    try {
        if (tracking.idProveedor == -1) {
            setTracking((prev) => ({
                ...prev,
                errores: [
                    {name: 'idProveedor', message: 'Debe seleccionar un proveedor.'}
                ]
            }));
        } else if (tracking.idProveedor == 1 || tracking.idProveedor == 2) { //proveedor = Aeropost
            setActualizando(true);
            const trackingParametro: TrackingCompleto = {
                ...tracking,
                ordenEstatus: 5,
            }
            setTracking(await ActualizarEstado(trackingParametro));
        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }
}

//Si presiona el btn OMB
export async function AccionOMB(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Cuando tocan el boton OMB no importa si viene de PA o de EN
    // 2. Validar que tenga el proveedor
    // 2.1. Lo que importa es si el proveedor es MiLocker o Aeropost
    // Si es ML, entonces poner tanto en status como statusSincronizado el nuevo estado
    // Si es AP, poner solo en status

    try {
        if (tracking.idProveedor == -1) {
            setTracking((prev) => ({
                ...prev,
                errores: [
                    {name: 'idProveedor', message: 'Debe seleccionar un proveedor.'}
                ]
            }));
        } else if (tracking.idProveedor == 1 || tracking.idProveedor == 2) { //proveedor = Aeropost
            setActualizando(true);
            const trackingParametro: TrackingCompleto = {
                ...tracking,
                ordenEstatus: 6,
            }
            setTracking(await ActualizarEstado(trackingParametro));
        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }
}

//Si presiona el btn EN
export async function AccionEN(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Cuando tocan el boton EN no importa si viene de OMB o de FDO
    // 2. Validar que tenga el proveedor
    // 3. Si viene de FDO a EN, enviar mensaje modal de si esta seguro de eliminar el archivo
    // 2.1. Lo que importa es si el proveedor es MiLocker o Aeropost
    // Si es ML, entonces poner tanto en status como statusSincronizado el nuevo estado
    // Si es AP, poner solo en status

    try {
        if (tracking.idProveedor == -1) {
            setTracking((prev) => ({
                ...prev,
                errores: [
                    {name: 'idProveedor', message: 'Debe seleccionar un proveedor.'}
                ]
            }));

        } // 2. Validar que tenga el proveedor
        else if (tracking.idProveedor == 1 || tracking.idProveedor == 2) { //proveedor = Aeropost


            // 3. Si viene de FDO a EN, enviar mensaje modal de si esta seguro de eliminar el archivo
            const estadoSiguiente: boolean = 7 - tracking.ordenEstatus === 1; // de SPR a PDO
            const estadoAnterior: boolean = 7 - tracking.ordenEstatus === -1; // de RMI a PDO

            //OMB -> EN
            if (estadoSiguiente){
                setActualizando(true);
                const trackingParametro: TrackingCompleto = {
                    ...tracking,
                    ordenEstatus: 7,
                }
                setTracking(await ActualizarEstado(trackingParametro));

                setActualizando(false);// Se deja hasta aca porque el proceso de eliminacion de archivo es extenso

            } //FDO -> EN
            else if(estadoAnterior){
                //eliminar factura y pasar a EN

                const respuestaAceptada = await SeguroModal({
                    titulo: 'Cambiar estado: FDO -> EN ',
                    description: '¿Está seguro de cambiar el estado de Facturado a Entregado? La factura se borrará.'
                });

                if (!respuestaAceptada) return;

                setTracking((prev) => ({
                    ...prev,
                    factura: null,
                    ordenEstatus: 7,
                }));
                await EliminarFactura(tracking);
            }

        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        console.log(e);
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    }
}

// OTRAS ACCIONES OTR
//Si presiona el btn Eliminar
export async function AccionEliminar(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){

    try {
            setActualizando(true);
            const trackingParametro: TrackingCompleto = {
                ...tracking,
                ordenEstatus: 25,
            }
            setTracking(await ActualizarEstado(trackingParametro));

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }
}


//Si presiona el btn Paquete Perdido
export async function AccionPaquetePerdido(setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, tracking: TrackingCompleto, setActualizando: React.Dispatch<React.SetStateAction<boolean>>){

    try {
        setActualizando(true);
        const trackingParametro: TrackingCompleto = {
            ...tracking,
            ordenEstatus: 20,
        }
        setTracking(await ActualizarEstado(trackingParametro));

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar el estado', 'Hubo un error para actualizar el estado del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setActualizando(false);
    }
}
