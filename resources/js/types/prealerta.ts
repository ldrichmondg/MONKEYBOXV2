import { ErroresInputs } from '@/types/input';

export interface PrealertaBase {
    id: number;
    descripcion: string;
    valor: number;
    idTrackingProveedor: number;
}

export interface PrealertaActualizar extends PrealertaBase, ErroresInputs {
    idTracking: string;
    idProveedor: number;
}

