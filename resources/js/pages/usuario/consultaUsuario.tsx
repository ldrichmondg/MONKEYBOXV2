import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { iconMap } from '@/lib/iconMap';
import { ContenidoModal } from '@/ownComponents/modals/eliminarModal';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { ExitoModal } from '@/ownComponents/modals/exitoModal';
import { BreadcrumbItem, ButtonHeader } from '@/types';
import { WithActions } from '@/types/table';
import { UsuarioTable } from '@/types/usuario';
import { Head, Link } from '@inertiajs/react';
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
import { ArrowUpDown, ChevronDown, LucideIcon, MoreHorizontal, RefreshCcw } from 'lucide-react';
import React, { useState } from 'react';
import { Xlsx } from '@/ownComponents/svgs/xlsx';
import * as XLSX from 'xlsx';
import { ObtenerUsuarios } from '@/api/usuario/usuario';
import { Spinner } from '@/ownComponents/spinner';

interface Props {
    usuarios: UsuarioTable[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consulta Usuarios',
        href: route('usuario.usuario.consulta.vista'),
    },
];

const buttons: ButtonHeader[] = [
    {
        id: 'registrarUsuario',
        name: 'Registrar Usuario',
        className: 'bg-red-400 text-white hover:bg-red-500 ',
        isActive: true,
        href: route('usuario.usuario.registro.vista'),
    },
];

export default function ConsultaUsuario({ usuarios }: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [usuariosFront, setUsuariosFront] = useState<UsuarioTable[]>(usuarios);

    // variables para exportar
    const [exportando, setExportando] = useState<boolean>(false);
    // variables para sincronizar
    const [sincronizando, setSincronizando] = useState<boolean>(false);

    //variables de tabla
    const columns: ColumnDef<UsuarioTable>[] = [
        {
            id: 'select',
            header: ({ table }) => (
                <Checkbox
                    checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                    onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                    aria-label="Select all"
                />
            ),
            cell: ({ row }) => (
                <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} aria-label="Select row" />
            ),
            enableSorting: false,
            enableHiding: false,
        },
        {
            accessorKey: 'cedula',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Cédula
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="capitalize">{row.getValue('cedula')}</div>,
            filterFn: (row, columnId, filterValue) => {
                const value = String(row.getValue(columnId) ?? '').toLowerCase();
                return value.includes(String(filterValue).toLowerCase());
            },
        },
        {
            accessorKey: 'nombre',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Nombre
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('nombre')}</div>,
        },
        {
            accessorKey: 'apellidos',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Apellidos
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('apellidos')}</div>,
        },
        {
            accessorKey: 'telefono',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Teléfono
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('telefono')}</div>,
            filterFn: (row, columnId, filterValue) => {
                const value = String(row.getValue(columnId) ?? '').toLowerCase();
                return value.includes(String(filterValue).toLowerCase());
            },
        },
        {
            accessorKey: 'correo',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Correo
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('correo')}</div>,
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const actions = (row.original as WithActions).actions ?? [];

                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <span className="sr-only">Open menu</span>
                                <MoreHorizontal />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                            {actions.map((action, index) => {
                                const LucideIcon: LucideIcon = iconMap[action.icon ?? 'UserPlus'];
                                const needsModal = action.actionMessage != undefined && action.actionModalTitle != undefined;
                                return (
                                    <DropdownMenuItem
                                        key={index}
                                        onClick={async () => {
                                            if (needsModal && action.actionMessage != undefined && action.actionModalTitle != undefined)
                                                await EliminarUsuario(row.original.id, row.original.nombre + ' ' + row.original.apellidos, setUsuariosFront);
                                        }}
                                    >
                                        <Link href={!needsModal ? action.route : ''} className="flex flex-nowrap items-center gap-2">
                                            <LucideIcon /> <span>{action.descripcion} </span>
                                        </Link>
                                    </DropdownMenuItem>
                                );
                            })}
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    const table = useReactTable<UsuarioTable>({
        data: usuariosFront,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
        getRowId: (row) => String(row.id), // ← forzamos id único
    });


    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title="Consulta Usuarios" />

            <div className="flex w-[100%] items-center justify-center">
                <Card className="w-[95%] p-0 md:my-3 lg:my-5">
                    <div className="w-[100%]">
                        <div id="HeaderTable" className="flex items-center justify-between p-4">
                            <span className="mr-2 font-semibold">Usuarios</span>

                            <div className="flex flex-1 items-center justify-end gap-2">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="outline" className="ml-1">
                                            Columns <ChevronDown />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        {table
                                            .getAllColumns()
                                            .filter((column) => column.getCanHide())
                                            .map((column) => {
                                                return (
                                                    <DropdownMenuCheckboxItem
                                                        key={column.id}
                                                        className="capitalize"
                                                        checked={column.getIsVisible()}
                                                        onCheckedChange={(value) => column.toggleVisibility(!!value)}
                                                    >
                                                        {column.id}
                                                    </DropdownMenuCheckboxItem>
                                                );
                                            })}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                                <Button
                                    className={'flex items-center border border-gray-100 bg-transparent text-base text-gray-500'}
                                    variant="outline"
                                    size="lg"
                                    disabled={exportando}
                                    onClick={() => ExportarUsuarios(usuariosFront, setExportando)}
                                >

                                    <Xlsx className={"!h-6 !w-6"} />

                                </Button>

                                <Button
                                    className={'flex items-center border border-gray-100 bg-transparent text-base text-gray-500'}
                                    variant="outline"
                                    size="lg"
                                    disabled={sincronizando}
                                    onClick={() => SincronizarUsuarios(setUsuariosFront, setSincronizando)}
                                >

                                    <RefreshCcw className={sincronizando ? '!h-6 !w-6 animate-spin' : '!h-6 !w-6'}/>

                                </Button>
                            </div>
                        </div>

                        <div className="border">
                            <Table>
                                <TableHeader>
                                    {table.getHeaderGroups().map((headerGroup) => (
                                        <TableRow key={headerGroup.id} className="bg-gray-300">
                                            {headerGroup.headers.map((header) => {
                                                return (
                                                    <TableHead key={header.id} className="p-3">
                                                        {header.isPlaceholder
                                                            ? null
                                                            : flexRender(header.column.columnDef.header, header.getContext())}

                                                        {header.id != 'actions' && header.id != 'select' ? (
                                                            <Input
                                                                placeholder=""
                                                                value={(table.getColumn(header.id)?.getFilterValue() as string) ?? ''}
                                                                onChange={(event) => table.getColumn(header.id)?.setFilterValue(event.target.value)}
                                                                className="max-w-sm"
                                                            />
                                                        ) : null}
                                                    </TableHead>
                                                );
                                            })}
                                        </TableRow>
                                    ))}
                                </TableHeader>
                                <TableBody>
                                    {table.getRowModel().rows?.length ? (
                                        table.getRowModel().rows.map((row) => (
                                            <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                                {row.getVisibleCells().map((cell) => (
                                                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                                ))}
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={columns.length} className="h-24 text-center">
                                                Sin Resultados.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                        <div className="flex items-center justify-end space-x-2 px-4 py-4">
                            <div className="flex-1 text-sm text-muted-foreground">
                                {table.getFilteredSelectedRowModel().rows.length} of {table.getFilteredRowModel().rows.length} row(s) selected.
                            </div>
                            <div className="space-x-2">
                                <Button variant="outline" size="sm" onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}>
                                    Anterior
                                </Button>
                                <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                                    Siguiente
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <Spinner isActive={sincronizando || exportando}></Spinner>
        </AppLayout>
    );
}

async function EliminarUsuario(
    id: number,
    nombreUsuario: string,
    setUsuarios: React.Dispatch<React.SetStateAction<UsuarioTable[]>>
) {
    try {
        const confirmarEliminar = await ContenidoModal(
            'Eliminar usuario',
            `Está seguro de eliminar el usuario: ${nombreUsuario}?`
        );

        if (!confirmarEliminar) return;

        const response = await fetch(route('usuario.usuario.eliminar.json'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ idUsuario: id }),
        });

        if (!response.ok) throw Error('Error al eliminar usuario');

        ExitoModal('Éxito', 'Se eliminó el usuario exitosamente.');

        setUsuarios(prev => prev.filter(u => String(u.id) !== String(id)));

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (error) {
        ErrorModal('Error al eliminar usuario', 'Hubo un error al eliminar usuario');
    }
}

function ExportarUsuarios(usuarios: UsuarioTable[], setExportando: React.Dispatch<React.SetStateAction<boolean>>) {
    // # Se exporta a un archivo XLSX
    // 1. Mostrar el spinner con setExportando(true)
    // 2. Hacer el archivo de excel
    // 3. Ocultar el spinner con setExportado(false)
    try{
        if (!usuarios || usuarios.length === 0) {
            ErrorModal('Error al exportar', 'No se puede exportar a un excel porque no hay registros de usuarios')
            return;
        }

        setExportando(true);
        // Convertimos los usuarios a un array de objetos planos
        const datos = usuarios.map((u) => ({
            Cédula: u.cedula,
            Nombre: u.nombre,
            Apellidos: u.apellidos,
            Teléfono: u.telefono,
            Correo: u.correo,
        }));

        // Creamos una hoja de Excel
        const ws = XLSX.utils.json_to_sheet(datos);

        // Creamos un libro de Excel
        const wb = XLSX.utils.book_new();
        const hoy = new Date();
        const dia = String(hoy.getDate()).padStart(2, '0'); // día con 2 dígitos
        const mes = String(hoy.getMonth() + 1).padStart(2, '0'); // mes con 2 dígitos (0-index)
        const anio = hoy.getFullYear();

        const nombreArchivo = `Usuarios_${dia}-${mes}-${anio}.xlsx`;

        XLSX.utils.book_append_sheet(wb, ws, 'Usuarios');

        // Guardamos el archivo
        XLSX.writeFile(wb, nombreArchivo);

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al exportar', 'Hubo un error al exportar. Favor volverlo a intentar o comunicarse con el departamento de TI');

    } finally {
        //Quirar el spinner
        setExportando(false);
    }

}

async function SincronizarUsuarios(setUsuariosFront: React.Dispatch<React.SetStateAction<UsuarioTable[]>>, setSincronizando: React.Dispatch<React.SetStateAction<boolean>>) {

    // # Sincronizar los usuarios en caso de que hubieran habido cambios
    try{

        setSincronizando(true);
        setUsuariosFront(await ObtenerUsuarios());

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    }catch (e) {
        ErrorModal('Error al consultar usuarios', 'Hubo un error al consultar empleados. Favor volver a intentarlo o consultar al departamento de TI');
    }finally {
        setSincronizando(false);
    }
}
