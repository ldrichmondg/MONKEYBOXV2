import { ResponseError } from '@/api/administracionErrores/administracionErrores';
import { ObtenerClientes } from '@/api/clientes/cliente';
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
import { ContenidoModal, EliminarModal } from '@/ownComponents/modals/eliminarModal';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { ExitoModal } from '@/ownComponents/modals/exitoModal';
import { Spinner } from '@/ownComponents/spinner';
import { Xlsx } from '@/ownComponents/svgs/xlsx';
import { BreadcrumbItem, ButtonHeader } from '@/types';
import { ClienteTable } from '@/types/cliente';
import { AppError } from '@/types/erroresExcepciones';
import { WithActions } from '@/types/table';
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
import React, { useEffect, useState } from 'react';
import * as XLSX from 'xlsx';
import { TrackingTable } from '@/types/tracking';

interface Props {
    clientes: ClienteTable[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consulta Clientes',
        href: route('usuario.cliente.consulta.vista'),
    },
];

const buttons: ButtonHeader[] = [
    {
        id: 'registrar',
        name: 'Registrar Cliente',
        className: 'bg-red-400 text-white hover:bg-red-500 ',
        isActive: true,
        href: route('usuario.cliente.registro.vista'),
    },
];

export default function ConsultaCliente({ clientes }: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [clientesFront, setClientesFront] = useState<ClienteTable[]>(clientes);
    const [trackingsCliente, setTrackingsCliente] = useState<boolean>(false);

    // variables para exportar
    const [exportando, setExportando] = useState<boolean>(false);
    // variables para sincronizar
    const [sincronizando, setSincronizando] = useState<boolean>(false);

    //variables de tabla
    const columns: ColumnDef<ClienteTable>[] = [
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
            accessorKey: 'casillero',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Casillero
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="capitalize">{row.getValue('casillero')}</div>,
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
                                                await EliminarCliente(
                                                    row.original.id,
                                                    row.original.nombre + ' ' + row.original.apellidos,
                                                    /*setClientesFront,*/
                                                    setTrackingsCliente
                                                );
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

    const table = useReactTable<ClienteTable>({
        data: clientesFront,
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

    // tabla de trackings en proceso del cliente
    /*const [sortingTracking, setSortingTracking] = useState<SortingState>([]);
    const [columnFiltersTracking, setColumnFiltersTracking] = useState<ColumnFiltersState>([]);
    const [columnVisibilityTracking, setColumnVisibilityTracking] = useState<VisibilityState>({});
    const [rowSelectionTracking, setRowSelectionTracking] = useState({});

    const columnsTracking: ColumnDef<TrackingTable>[] = [
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
            accessorKey: 'idTracking',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Tracking ID
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="capitalize">{row.getValue('idTracking')}</div>,
        },
        {
            accessorKey: 'nombreCliente',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Cliente
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('nombreCliente')}</div>,
        },
        {
            accessorKey: 'descripcion',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Descripcion
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('descripcion')}</div>,
        },
        {
            accessorKey: 'desde',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Desde
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('desde')}</div>,
        },
        {
            accessorKey: 'hasta',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Hasta
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('hasta')}</div>,
        },
        {
            accessorKey: 'destino',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Destino
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('destino')}</div>,
        },
        {
            accessorKey: 'couriers',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Couriers
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('couriers')}</div>,
        },
        {
            accessorKey: 'estatus',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Estatus
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => (
                <Badge className={'text-white lowercase ' + row.original.estatus.colorClass} variant="secondary">
                    {row.original.estatus.descripcion}
                </Badge>
            ),
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
                                                EliminarModal({
                                                    description: action.actionMessage,
                                                    titulo: action.actionModalTitle,
                                                    route: action.route,
                                                });
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

    const tableTracking = useReactTable<TrackingTable>({
        data: trackingsCliente,
        columns: columnsTracking,
        onSortingChange: setSortingTracking,
        onColumnFiltersChange: setColumnFiltersTracking,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibilityTracking,
        onRowSelectionChange: setRowSelectionTracking,
        state: {
            sorting: sortingTracking,
            columnFilters: columnFiltersTracking,
            columnVisibility: columnVisibilityTracking,
            rowSelection: rowSelectionTracking,
        },
        getRowId: (row) => String(row.id), // ← forzamos id único
    });
*/
    useEffect(() => {
        console.log(trackingsCliente);
    }, [trackingsCliente]);

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title="Consulta Clientes" />

            <div className="flex w-[100%] items-center justify-center">
                <Card className="w-[95%] p-0 md:my-3 lg:my-5">
                    <div className="w-[100%]">
                        <div id="HeaderTable" className="flex items-center justify-between p-4">
                            <span className="mr-2 font-semibold">Clientes</span>

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
                                    onClick={() => ExportarClientes(clientesFront, setExportando)}
                                >
                                    <Xlsx className={'!h-6 !w-6'} />
                                </Button>

                                <Button
                                    className={'flex items-center border border-gray-100 bg-transparent text-base text-gray-500'}
                                    variant="outline"
                                    size="lg"
                                    disabled={sincronizando}
                                    onClick={() => SincronizarClientes(setClientesFront, setSincronizando)}
                                >
                                    <RefreshCcw className={sincronizando ? '!h-6 !w-6 animate-spin' : '!h-6 !w-6'} />
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

            {/*<Dialog open={trackingsCliente.length > 0} >{/* onOpenChange={() => setTrackingCliente([])} }
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Trackings En Proceso del cliente</DialogTitle>
                        <DialogDescription>Los siguientes trackings siguen en proceso. Favor eliminar estos trackings antes de eliminar el cliente:</DialogDescription>
                    </DialogHeader>

                    <Table>
                        <TableHeader>
                            {tableTracking.getHeaderGroups().map((headerGroup) => (
                                <TableRow key={headerGroup.id} className="bg-gray-300">
                                    {headerGroup.headers.map((header) => {
                                        return (
                                            <TableHead key={header.id} className="p-3">
                                                {header.isPlaceholder
                                                    ? null
                                                    : flexRender(header.column.columnDef.header, header.getContext())}

                                                {header.id != 'actions' && header.id != 'select' ?

                                                    <Input
                                                        placeholder=""
                                                        value={(tableTracking.getColumn(header.id)?.getFilterValue() as string) ?? ''}
                                                        onChange={(event) => tableTracking.getColumn(header.id)?.setFilterValue(event.target.value)}
                                                        className="max-w-sm "
                                                    /> : null
                                                }

                                            </TableHead>
                                        );
                                    })}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {tableTracking.getRowModel().rows?.length ? (
                                tableTracking.getRowModel().rows.map((row) => (
                                    <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={columns.length} className="h-24 text-center">
                                        Sin resultados.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline">Cancelar</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog> */}

            <Spinner isActive={sincronizando || exportando}></Spinner>
        </AppLayout>
    );
}

async function EliminarCliente(id: number, nombreUsuario: string, setTrackingsCliente: React.Dispatch<React.SetStateAction<boolean>>) {
    try {
        const confirmarEliminar = await ContenidoModal('Eliminar cliente', `Está seguro de eliminar el cliente: ${nombreUsuario}?`);

        if (!confirmarEliminar) return;

        const response = await fetch(route('usuario.cliente.eliminar.json'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ idCliente: id }),
        });

        if (!response.ok) {
            const errorResponse: ResponseError = await response.json();
            throw new AppError(
                errorResponse.errorApp,
                response.status,
                'Error al eliminar el cliente',
                errorResponse.titleMessage,
                errorResponse.data,
            );
        }

        ExitoModal('Éxito', 'Se eliminó el cliente exitosamente.');

        //setClientes((prev) => prev.filter((u) => String(u.id) !== String(id)));

    } catch (error) {
        if (error instanceof AppError) {
            console.log(error.data.trackingsEnProceso)
            const trackingsEj: TrackingTable[] = error.data.trackingsEnProceso;
            const trackings: TrackingTable[] = [
                {
                    id: -1,
                    idTracking: '2323',
                    nombreCliente: 'Cliente P',
                    descripcion: 'DESC',
                    desde: 'A',
                    hasta: 'B',
                    destino: 'C',
                    couriers: 'D',
                    trackingCompleto: true,
                    estatus: {
                        descripcion: 'hola',
                        colorClass: 'bg-gray-200'
                    },
                    actions: []
                }
            ]
            setTrackingsCliente(true);
        }

        //ErrorModal('Error al eliminar cliente', 'Hubo un error al eliminar cliente');
    } finally {
        setTrackingsCliente(true);
    }
}

function ExportarClientes(clientes: ClienteTable[], setExportando: React.Dispatch<React.SetStateAction<boolean>>) {
    // # Se exporta a un archivo XLSX
    // 1. Mostrar el spinner con setExportando(true)
    // 2. Hacer el archivo de excel
    // 3. Ocultar el spinner con setExportado(false)
    try {
        if (!clientes || clientes.length === 0) {
            ErrorModal('Error al exportar', 'No se puede exportar a un excel porque no hay registros de usuarios');
            return;
        }

        setExportando(true);
        // Convertimos los usuarios a un array de objetos planos
        const datos = clientes.map((u) => ({
            Casillero: u.casillero,
            Cédula: u.cedula,
            Nombre: u.nombre,
            Apellidos: u.apellidos,
            Teléfono: u.telefono,
            Correo: u.correo,
            DireccionPrincipal: u.direccionPrincipal,
        }));

        // Creamos una hoja de Excel
        const ws = XLSX.utils.json_to_sheet(datos);

        // Creamos un libro de Excel
        const wb = XLSX.utils.book_new();
        const hoy = new Date();
        const dia = String(hoy.getDate()).padStart(2, '0'); // día con 2 dígitos
        const mes = String(hoy.getMonth() + 1).padStart(2, '0'); // mes con 2 dígitos (0-index)
        const anio = hoy.getFullYear();

        const nombreArchivo = `Clientes_${dia}-${mes}-${anio}.xlsx`;

        XLSX.utils.book_append_sheet(wb, ws, 'Clientes');

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

async function SincronizarClientes(
    setClientesFront: React.Dispatch<React.SetStateAction<ClienteTable[]>>,
    setSincronizando: React.Dispatch<React.SetStateAction<boolean>>,
) {
    // # Sincronizar los clientes en caso de que hubieran habido cambios
    try {
        setSincronizando(true);
        setClientesFront(await ObtenerClientes());

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al consultar clientes', 'Hubo un error al consultar clientes. Favor volver a intentarlo o consultar al departamento de TI');
    } finally {
        setSincronizando(false);
    }
}
