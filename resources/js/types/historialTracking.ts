import { WithActions } from '@/types/table';

export interface HistorialTracking extends WithActions{
    descripcion: string;
    descripcionModificada: string;
    codigoPostal: number,
    paisEstado: number;
    ocultado: boolean;
    tipo: number;
    fecha: Date;
    idTracking: number;
    perteneceEstado: string|null;
}
