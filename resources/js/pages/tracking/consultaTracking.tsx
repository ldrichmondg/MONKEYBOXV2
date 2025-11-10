import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, ButtonHeader } from '@/types';
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
import {EliminarModal} from '@/ownComponents/modals/eliminarModal';
import { ArrowUpDown, ChevronDown, LucideIcon, MoreHorizontal, RefreshCcw } from 'lucide-react';

import { Badge } from "@/components/ui/badge"
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { Card } from '@/components/ui/card';

import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import * as React from 'react';

import { iconMap } from '@/lib/iconMap';
import { TrackingTable } from '@/types/tracking';
import { WithActions } from '@/types/table';
import { useEffect, useState } from 'react';
import { ClienteTable } from '@/types/cliente';
import { ObtenerClientes } from '@/api/clientes/cliente';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { ObtenerTrackingsConsultadosTable } from '@/api/tracking/consultarTrackings';
import { Spinner } from '@/ownComponents/spinner';

interface Props {
    trackings: TrackingTable[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consulta Trackings',
        href: route('tracking.consulta.vista'),
    },
];

const buttons: ButtonHeader[] = [
    {
        id: 'registrarTrackingsMasivo',
        name: 'Registrar Trackings Masivo',
        className: 'bg-red-400 text-white hover:bg-red-500 ',
        isActive: true,
        href: route('tracking.registroMasivo.vista'),
    },
];

export default function ConsultaTracking({trackings}: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});
    const [trackingsFront, setTrackings] = useState<TrackingTable[]>(trackings);

    const table = useReactTable({
        data: trackingsFront,
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
    });

    const [sincronizando, setSincronizando] = useState<boolean>(false);

    useEffect(() => {
        // sincronizar cambios ya que no se van a enviar los +3500 trackings
        let isMounted = true;

        const cargar = async () => {
            const cached = localStorage.getItem('trackings');

            if (cached) {
                const trackingsLS = JSON.parse(cached);
                if (isMounted) setTrackings(trackingsLS);

            }else{
                // aunque los trackings esten en cache, se vuelve a sincronizar
                setSincronizando(true);
                try {
                    const trackings = await SincronizarTrackings(setTrackings, setSincronizando);
                    if (isMounted) {
                        setTrackings(trackings);
                    }
                } finally {
                    if (isMounted) setSincronizando(false);
                }
            }

        };

        cargar();
        return () => { isMounted = false; };
    }, []);

    useEffect(() => {
        console.log(trackingsFront)
    }, [trackingsFront]);


    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title="Consulta Tracking" />

            <div className="flex w-[100%] items-center justify-center">
                <Card className="w-[95%] p-0 md:my-3 lg:my-5">
                    <div className="w-[100%]">
                        <div id="HeaderTable" className="flex items-center justify-between p-4">
                            <span className="mr-2 font-semibold">Trackings</span>

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
                                    disabled={sincronizando}
                                    onClick={() => SincronizarTrackings(setTrackings, setSincronizando)}
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

                                                        {header.id != 'actions' && header.id != 'select' ?

                                                            <Input
                                                                placeholder=""
                                                                value={(table.getColumn(header.id)?.getFilterValue() as string) ?? ''}
                                                                onChange={(event) => table.getColumn(header.id)?.setFilterValue(event.target.value)}
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
                                                No results.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                        <div className="flex items-center justify-end space-x-2 py-4 px-4">
                            <div className="flex-1 text-sm text-muted-foreground">
                                {table.getFilteredSelectedRowModel().rows.length} of {table.getFilteredRowModel().rows.length} row(s) selected.
                            </div>
                            <div className="space-x-2">
                                <Button variant="outline" size="sm" onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}>
                                    Previous
                                </Button>
                                <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            {/*<Spinner isActive={sincronizando}></Spinner>*/}
        </AppLayout>
    );
}

const columns: ColumnDef<TrackingTable>[] = [
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
        accessorKey: 'trackingMBox',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    T. MBox
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('trackingMBox')}</div>,
    },
    {
        accessorKey: 'trackingProveedor',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    T. Proveedor
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('trackingProveedor')}</div>,
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Descripcion
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="lowercase">{row.getValue('descripcion')}</div>,
    },
    {
        accessorKey: 'ultimoHistorialTracking',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Ult. Historial Tracking
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="lowercase">{row.getValue('ultimoHistorialTracking')}</div>,
    },
    {
        accessorKey: 'ultimaActualizacion',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Ult. Actualización
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="lowercase">{row.getValue('ultimaActualizacion')}</div>,
    },
    {
        accessorKey: 'couriers',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Couriers
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <div className="lowercase">{row.getValue('couriers')}</div>,
    },
    {
        id: 'estatus',
        accessorFn: (row) => row.estatus.descripcion,
        header: ({ column }) => {
            return (
                <Button
                    variant="ghost"
                    className="text-gray-500"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    Estatus
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => (
            <Badge
                className={"lowercase text-white " + row.original.estatus.colorClass}
                variant="secondary"
            >
                {row.original.estatus.descripcion}
            </Badge>
        ),
        filterFn: (row, columnId, filterValue) => {
            return row
                .getValue(columnId)
                ?.toLowerCase()
                .includes(filterValue.toLowerCase());
        },
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
                            const LucideIcon: LucideIcon = iconMap[action.icon ?? "UserPlus"]
                            const needsModal = (action.actionMessage != undefined && action.actionModalTitle != undefined);
                            return (
                                <DropdownMenuItem key={index} onClick={async () => { if(needsModal && action.actionMessage != undefined && action.actionModalTitle != undefined) EliminarModal({ description: action.actionMessage, titulo: action.actionModalTitle, route: action.route})} }>
                                    <Link href={!needsModal ? action.route : ''} className="flex flex-nowrap items-center gap-2">
                                        <LucideIcon/> <span>{action.descripcion} </span>
                                    </Link>
                                </DropdownMenuItem>
                            )
                        })
                        }

                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];


async function SincronizarTrackings(
    setTrackings: React.Dispatch<React.SetStateAction<TrackingTable[]>>,
    setSincronizando: React.Dispatch<React.SetStateAction<boolean>>
    ):Promise<TrackingTable[]>{
    try {
        setSincronizando(true);
        const trackings = await ObtenerTrackingsConsultadosTable();
        setTrackings(trackings);

        localStorage.setItem('trackings', JSON.stringify(trackings));

        return trackings;

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al consultar trackings', 'Hubo un error al consultar trackings. Favor volver a intentarlo o consultar al departamento de TI');
        return [];
    } finally {
        setSincronizando(false);
    }
}
