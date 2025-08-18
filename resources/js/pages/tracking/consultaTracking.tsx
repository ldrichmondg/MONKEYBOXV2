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
import { ArrowUpDown, ChevronDown, LucideIcon, MoreHorizontal } from 'lucide-react';

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

export default function ConsultaTracking() {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});

    const table = useReactTable({
        data,
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

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title="Consulta Tracking" />

            <div className="flex w-[100%] items-center justify-center">
                <Card className="w-[95%] p-0 md:my-3 lg:my-5">
                    <div className="w-[100%]">
                        <div id="HeaderTable" className="flex items-center justify-between p-4">
                            <span className="mr-2 font-semibold">Trackings</span>

                            <div className="flex flex-1 items-center justify-end">
                                <Input
                                    placeholder="Filter emails..."
                                    value={(table.getColumn('email')?.getFilterValue() as string) ?? ''}
                                    onChange={(event) => table.getColumn('email')?.setFilterValue(event.target.value)}
                                    className="max-w-sm"
                                />
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

        </AppLayout>
    );
}

const data: TrackingTable[] = [
    {
        id: 1,
        idTracking: '420456535453664',
        nombreCliente: 'Luis Richmond',
        descripcion: 'ROPA',
        desde: '',
        hasta: 'MIAMI, FL 4092',
        destino: '',
        couriers: 'USPS, DHL',
        estatus: {descripcion: 'Sin Preealerta', colorClass: 'bg-transparent border-pink-300 text-pink-300'},
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            },
            {
                descripcion: 'Eliminar',
                icon: 'Trash2',
                route: route('dashboard'),
                actionType: 'Eliminar',
                actionMessage: 'Estas seguro de eliminar el tracking #420456535453664 ?',
                actionModalTitle: 'Eliminar Tracking',
                isActive: true
            },
        ]

    },
    {
        id: 2,
        idTracking: '994488112233',
        nombreCliente: 'María González',
        descripcion: 'Zapatos deportivos',
        desde: 'Warehouse LA',
        hasta: 'San José, CR',
        destino: 'Pavas',
        couriers: 'FedEx',
        estatus: { descripcion: 'Entregado', colorClass: 'bg-transparent border-purple-300 text-purple-300' },
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            },
            {
                descripcion: 'Eliminar',
                icon: 'Trash2',
                route: route('dashboard'),
                actionType: 'Eliminar',
                actionMessage: '¿Seguro que desea eliminar el tracking #994488112233?',
                actionModalTitle: 'Eliminar Tracking',
                isActive: true
            },
        ]
    },
    {
        id: 3,
        idTracking: '558899776655',
        nombreCliente: 'Carlos Méndez',
        descripcion: 'Electrónica',
        desde: '',
        hasta: 'Heredia, CR',
        destino: 'Heredia centro',
        couriers: 'UPS',
        estatus: { descripcion: 'Tránsito CR', colorClass: 'bg-transparent border-blue-400 text-blue-400' },
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            }
        ]
    },
    {
        id: 4,
        idTracking: '882244668800',
        nombreCliente: 'Fernanda Araya',
        descripcion: 'Libros y papelería',
        desde: 'Amazon',
        hasta: 'Alajuela, CR',
        destino: 'Grecia',
        couriers: 'Amazon Logistics',
        estatus: { descripcion: 'Facturado', colorClass: 'bg-transparent border-green-400 text-green-400' },
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            }
        ]
    },
    {
        id: 5,
        idTracking: '223344556677',
        nombreCliente: 'Kevin Mora',
        descripcion: 'Componentes de PC',
        desde: 'Newegg',
        hasta: '',
        destino: '',
        couriers: 'DHL',
        estatus: { descripcion: 'Recibido Miami', colorClass: 'bg-transparent border-sky-400 text-sky-400' },
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            },
            {
                descripcion: 'Eliminar',
                icon: 'Trash2',
                route: route('dashboard'),
                actionType: 'Eliminar',
                actionMessage: '¿Seguro que desea eliminar el tracking #223344556677?',
                actionModalTitle: 'Eliminar Tracking',
                isActive: false
            },
        ]
    },
    {
        id: 6,
        idTracking: '776655443322',
        nombreCliente: 'Andrea Navarro',
        descripcion: 'Accesorios para mascotas',
        desde: 'Etsy',
        hasta: 'Cartago, CR',
        destino: 'El Tejar',
        couriers: 'USPS',
        estatus: { descripcion: 'Proceso Aduana', colorClass: 'bg-transparent border-gray-200 text-gray-200' },
        actions: [
            {
                descripcion: 'Detalle',
                icon: 'Edit',
                route: route('dashboard'),
                actionType: 'GET',
                isActive: true
            },
            {
                descripcion: 'Eliminar',
                icon: 'Trash2',
                route: route('dashboard'),
                actionType: 'Eliminar',
                actionMessage: '¿Está seguro de eliminar el tracking #776655443322?',
                actionModalTitle: 'Eliminar Tracking',
                isActive: true
            }
        ]
    }





];

export const columns: ColumnDef<TrackingTable>[] = [
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
        accessorKey: 'desde',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
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
                <Button variant="ghost" className="text-gray-500 " onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Estatus
                    <ArrowUpDown />
                </Button>
            );
        },
        cell: ({ row }) => <Badge className={"lowercase text-white " + row.original.estatus.colorClass} variant="secondary">{row.original.estatus.descripcion}</Badge>
    ,
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const object = row.original;
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
                        {actions.map((action) => {
                            const LucideIcon: LucideIcon = iconMap[action.icon ?? "UserPlus"]
                            const needsModal = (action.actionMessage != undefined && action.actionModalTitle != undefined);
                            return (
                                <DropdownMenuItem key={object.id} onClick={async () => { if(needsModal && action.actionMessage != undefined && action.actionModalTitle != undefined) EliminarModal({ description: action.actionMessage, titulo: action.actionModalTitle, route: action.route})} }>
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
