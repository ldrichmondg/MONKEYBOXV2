export interface WithActions {
    actions: ActionTable[];
}

export interface ActionTable {
    descripcion: string;
    icon: string; // aqui se va a despues pasar del nombre de string a el LucideIcon
    route: string;
    actionType: string;
    actionMessage?: string;
    actionModalTitle?: string;
    isActive: boolean;
}

export interface EstatusTable {
    descripcion: string;
    colorClass: string;
}
