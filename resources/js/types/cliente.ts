import { EstatusTable, WithActions } from '@/types/table';
import { ErroresInputs } from '@/types/input';
import { Usuario } from '@/types/usuario';


export interface Cliente extends Usuario {
    //el id de la interfaz usuario es el idCliente
    casillero: string;
    direccionPrincipal: string;
}


export interface ClienteTable extends Cliente, WithActions {

}

export interface ClienteTracking {
    id: number;
    nombre: string;
    apellidos: string;
    telefono: number;
}

export interface ClienteCompleto extends Cliente, ErroresInputs{
    direcciones: DireccionesTable[];
    fechaNacimiento?: Date | null;
}

export interface Direccion {
    id: number;
    direccion: string;
    tipo: number;
    idCliente: number;
    codigoPostal: string;
    paisEstado: string;
    linkWaze: string;
}

export interface DireccionesTable extends Direccion, WithActions {
    tipoStatus: EstatusTable;
}

export interface ConstruccionCodigoPostal extends ErroresInputs {
    id: number;
    idProvincia: number;
    idCanton: number;
    idDistrito: number;
    codigoPostal: number;
    direccion: string;
    linkWaze: string;
    tipoDireccion: number;
    modoRegistro: boolean; //true si registra, false si actualiza
}

