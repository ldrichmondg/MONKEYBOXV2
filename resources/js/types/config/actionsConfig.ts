import { LucideIcon, SquarePen } from 'lucide-react';

export interface Accion {
    nombreAccion: string;
    label: string;
    icon: LucideIcon
}

export const accionesHistorialTracking: Accion[] = [
    {nombreAccion: 'editar', label: 'Editar', icon: SquarePen },
    {nombreAccion: 'ocultar', label: 'Ocultar', icon: SquarePen },
    ];
