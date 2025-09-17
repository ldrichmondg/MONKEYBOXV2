/*
 Archivo donde se encuentra toda la logica de las acciones de detalle tracking
 */

import { SeguroModal } from '@/ownComponents/modals/seguroModal';
import { toast } from 'sonner';
import { TrackingCompleto, TrackingConPrealertaBaseProveedor } from '@/types/tracking';
import { PrealertarTracking } from '@/api/tracking/prealertarTracking';
import React from 'react';
import { PrealertaActualizar } from '@/types/prealerta';
import { ActualizarPrealerta } from '@/api/prealerta/prealerta';
import { EstadoMBox } from '@/types/estadoMBox';
import { AppError} from '@/types/erroresExcepciones';

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
