
import { WithActions, EstatusTable } from './table';
import { PrealertaBase } from '@/types/prealerta';
import {ErroresInputs} from '@/types/input';
import { HistorialTracking } from '@/types/historialTracking';
import { ClienteTracking } from '@/types/cliente';
import { Imagen } from '@/types/imagenes';

export interface TrackingBase {
    id: number;
    idTracking: string;
    trackingProveedor: string;
    nombreCliente: string;
    descripcion: string; //preguntar si esta desc. es la igual q la de prealerta
    desde: string | null;
    hasta: string | null;
    destino: string | null;
    couriers: string;
    trackingCompleto?: boolean;
}

export interface TrackingConsultadosTable extends TrackingTable, ErroresInputs {
    valor: number;
    proveedor: string;
    idProveedor?: number;
    idCliente: number;
}

export interface TrackingRegistroTable extends TrackingTable {
    trackingProveedor: string;
    nombreProveedor: string;
}

export interface TrackingTable extends TrackingBase, WithActions {
    ultimoHistorialTracking: string;
    trackingMBox: string;
    estatus: EstatusTable;
}

export interface TrackingConProveedor extends TrackingBase {
    proveedor: string;
    idProveedor: number;
    trackingProveedor: string;
}

export interface TrackingConPrealertaBase extends TrackingBase {
    prealerta: PrealertaBase
}

export interface TrackingConPrealertaBaseProveedor extends TrackingConProveedor, TrackingConPrealertaBase, ErroresInputs, TrackingTable{

}

export interface TrackingCompleto extends TrackingBase, ErroresInputs {
    peso: number;
    idProveedor: number;
    idCliente: number;
    observaciones: string;
    estatus: string;
    ordenEstatus: number;
    estadoSincronizado: string;
    ordenEstatusSincronizado: number;
    historialesTracking: HistorialTracking[];
    nombreProveedor: string;
    trackingProveedor: string;
    diasTransito: number;
    idDireccion: number;
    valorPrealerta: number;

    cliente: ClienteTracking;
    imagenes: Imagen[];
    factura: File | string | null;
}




