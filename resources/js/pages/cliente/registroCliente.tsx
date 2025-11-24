import { RegistrarCliente } from '@/api/clientes/cliente';
import { obtenerCantones, obtenerCodigoPostal, obtenerDistritos, obtenerProvinciaCantonDistrito } from '@/api/direccion/direccion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { iconMap } from '@/lib/iconMap';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import InputFloatingLabel from '@/ownComponents/inputFloatingLabels';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { ExitoModal } from '@/ownComponents/modals/exitoModal';
import { Spinner } from '@/ownComponents/spinner';
import { ValidarCamposDireccion } from '@/pages/cliente/crudClienteAux';
import { type BreadcrumbItem, ButtonHeader, ComboBoxItem } from '@/types';
import { ClienteCompleto, ConstruccionCodigoPostal, DireccionesTable } from '@/types/cliente';
import { AppError } from '@/types/erroresExcepciones';
import { WithActions } from '@/types/table';
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import {
    ColumnDef,
    ColumnFiltersState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    SortingState,
    useReactTable,
    VisibilityState,
} from '@tanstack/react-table';
import { ArrowUpDown, ChevronDown, LucideIcon, MoreHorizontal, Plus } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import ClienteForm from '@/pages/cliente/registro/clienteForm';


export default function RegistroCliente() {
    const [registrando, setRegistrando] = React.useState<boolean>(false);
    const [clienteFront, setClienteFront] = React.useState<ClienteCompleto>({
        id: -1,
        nombre: '',
        apellidos: '',
        empresa: '',
        telefono: null,
        correo: '',
        cedula: null,
        casillero: '',
        direccionPrincipal: '',
        direcciones: [],
        fechaNacimiento: null,
        errores: [],
    });

    const [direcciones, setDirecciones] = React.useState<DireccionesTable[]>([]);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Consulta Clientes',
            href: route('usuario.cliente.consulta.vista'),
        },
        {
            title: 'Registrar Clientes',
            href: route('usuario.cliente.registro.vista'),
        },
    ];
    const buttons: ButtonHeader[] = [
        {
            id: 'registro',
            name: 'Registrar Cliente',
            className: 'bg-red-400 text-white hover:bg-red-500 ',
            isActive: true,
            onClick: () => RegistrarClienteAux(clienteFront, setClienteFront, setRegistrando, direcciones),
        },
    ];

    useEffect(() => {
        console.log(clienteFront);
    }, [clienteFront]);

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title={clienteFront.nombre} />
            <MainContainer>
                <ClienteForm clienteFront={clienteFront} setClienteFront={setClienteFront} direcciones={direcciones} setDirecciones={setDirecciones}/>
            </MainContainer>

            <Spinner isActive={registrando}></Spinner>
        </AppLayout>
    );
}

async function RegistrarClienteAux(
    cliente: ClienteCompleto,
    setCliente: React.Dispatch<React.SetStateAction<ClienteCompleto>>,
    setRegistrando: React.Dispatch<React.SetStateAction<boolean>>,
    direcciones: DireccionesTable[],
) {
    // 1. Mostrar el registrando para que se muestre el spinner
    // 2. Agregar las direcciones al cliente
    // 3. Guardar el cliente
    // 4. Enviar mensaje de que el cliente se registro exitosamente
    // 5. Quitar el spinner
    // 6. Cambiar a la vista de actualizar

    try {
        // 1. Mostrar el registrando para que se muestre el spinner
        setRegistrando(true);

        // 2. Agregar las direcciones al cliente
        const clienteActualizado = {
            ...cliente,
            direcciones: direcciones,
        };

        // 3. Guardar el cliente
        const clienteRespuesta = await RegistrarCliente(clienteActualizado);
        console.log(clienteRespuesta);

        setCliente(clienteRespuesta);
        if (clienteRespuesta.errores.length == 0) {
            ExitoModal('Registro exitoso', 'Se registró el cliente exitosamente.');

            // 6. Cambiar a la vista de actualizar
            router.visit(route('usuario.cliente.detalle.vista', { id: clienteRespuesta.id }));
        } else {
            const erroresDirecciones = clienteRespuesta.errores
                .filter((error) => error.name.includes('direcciones'))
                .map((error) => error.message)
                .join(' ');

            ErrorModal('Error al registrar cliente', erroresDirecciones);
        }

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        console.log(e);
        ErrorModal('Error al registrar cliente', 'Hubo un error al registrar el cliente. Vuelve a intentarlo o contacta a soporte TI.');
    } finally {
        // 5. Quitar el spinner
        setRegistrando(false);
    }
}
