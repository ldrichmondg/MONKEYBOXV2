import { WithActions } from '@/types/table';
import { ErroresInputs } from '@/types/input';


export interface Usuario {
    id: number;
    nombre: string;
    apellidos: string;
    empresa: string;
    telefono: number | null;
    correo: string;
    cedula: number | null;
}


export interface UsuarioTable extends Usuario, WithActions {

}

export interface UsuarioCompleto extends Usuario, ErroresInputs{

}
