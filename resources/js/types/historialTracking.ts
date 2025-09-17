import { WithActions } from '@/types/table';

export interface HistorialTracking extends WithActions {
    id:number;
    descripcion: string;
    descripcionModificada: string;
    codigoPostal: number;
    paisEstado: string;
    ocultado: boolean;
    tipo: number;
    fecha: string;
    hora: string;
    idTracking: number;
    perteneceEstado: string | null;
    fechaCompleta?: Date;
}
