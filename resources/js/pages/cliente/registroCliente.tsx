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

interface Props {
    provincias: ComboBoxItem[];
    tiposDirecciones: ComboBoxItem[];
}

export default function RegistroCliente({ provincias, tiposDirecciones }: Props) {
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
    const [abrirModalDireccionRegistro, setAbrirModalDireccionRegistro] = React.useState<boolean>(false);

    // Usados para la tabla
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState({});

    const [direcciones, setDirecciones] = React.useState<DireccionesTable[]>([]);

    const columns: ColumnDef<DireccionesTable>[] = [
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
            accessorKey: 'direccion',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Dirección
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="capitalize">{row.getValue('direccion')}</div>,
        },
        {
            accessorKey: 'tipoStatus',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Tipo
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => (
                <Badge className={'text-white lowercase ' + row.original.tipoStatus.colorClass} variant="secondary">
                    {row.original.tipoStatus.descripcion}
                </Badge>
            ),
        },
        {
            accessorKey: 'codigoPostal',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Código Postal
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="lowercase">{row.getValue('codigoPostal')}</div>,
        },
        {
            accessorKey: 'paisEstado',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Pais-Estado
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="">{row.getValue('paisEstado')}</div>,
        },
        {
            accessorKey: 'linkWaze',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Link Waze
                        <ArrowUpDown />
                    </Button>
                );
            },
            cell: ({ row }) => (
                <a
                    className="text-orange-400 lowercase"
                    href={row.getValue('linkWaze')}
                    target="_blank"
                    rel="noopener noreferrer"
                    onClick={(e) => e.stopPropagation()} // evita que Inertia maneje el click
                >
                    Link
                </a>
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
                                return (
                                    <DropdownMenuItem
                                        key={index}
                                        onClick={async () => {
                                            if (action.descripcion === 'Detalle') {
                                                DetalleDireccion(
                                                    setConstruccionDireccion,
                                                    row.original,
                                                    setAbrirModalDireccionRegistro,
                                                    setCantones,
                                                    setDistritos,
                                                );
                                            } else if (action.descripcion === 'Eliminar') {
                                                EliminarDireccion(row.original, setDirecciones);
                                            }
                                        }}
                                    >
                                        <div className="flex flex-nowrap items-center gap-2">
                                            <LucideIcon /> <span>{action.descripcion} </span>
                                        </div>
                                    </DropdownMenuItem>
                                );
                            })}
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    const table = useReactTable<DireccionesTable>({
        data: direcciones,
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
    //
    // -Construccion de direcciones
    const [cantones, setCantones] = useState<ComboBoxItem[]>([]);
    const [distritos, setDistritos] = useState<ComboBoxItem[]>([]);
    const [construccionDireccion, setConstruccionDireccion] = useState<ConstruccionCodigoPostal>({
        id: -1,
        idProvincia: -1,
        idCanton: -1,
        idDistrito: -1,
        codigoPostal: 0,
        direccion: '',
        linkWaze: '',
        tipoDireccion: -1,
        errores: [],
        modoRegistro: true,
    });
    const consDirVacia: ConstruccionCodigoPostal = {
        id: -1,
        idProvincia: -1,
        idCanton: -1,
        idDistrito: -1,
        codigoPostal: 0,
        direccion: '',
        linkWaze: '',
        tipoDireccion: -1,
        errores: [],
        modoRegistro: true,
    }; //me ayuda a llamar a esta direccion para limpiar todos los campos

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
                <div className="flex flex-col items-center justify-center gap-4 py-3">
                    <Card className="flex h-auto w-[95%] max-w-[95%] flex-col p-0 md:w-[80%] md:max-w-[80%]">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Cliente</p>
                        </CardHeader>

                        <CardContent className={'grid grid-cols-1 justify-between gap-3 px-4 pt-0 pb-5 md:grid-cols-2 xl:grid-cols-3'}>
                            <InputFloatingLabel
                                id="nombre"
                                type="text"
                                label="Nombre"
                                value={clienteFront.nombre}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, nombre: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'nombre')}
                            />

                            <InputFloatingLabel
                                id="apellidos"
                                type="text"
                                label="Apellidos"
                                value={clienteFront.apellidos}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, apellidos: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'apellidos')}
                            />

                            <InputFloatingLabel
                                id="cedula"
                                type="number"
                                label="Cédula"
                                value={Number(clienteFront.cedula)}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    const cedula = Number(e.target.value);
                                    setClienteFront((prev) => ({ ...prev, cedula: cedula }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'cedula')}
                            />

                            <InputFloatingLabel
                                id="correo"
                                type="text"
                                label="Correo"
                                value={clienteFront.correo}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, correo: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'correo')}
                            />

                            <InputFloatingLabel
                                id="telefono"
                                type="text"
                                label="Teléfono"
                                value={Number(clienteFront.telefono)}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    const telefono = Number(e.target.value);
                                    setClienteFront((prev) => ({ ...prev, telefono: telefono }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'telefono')}
                            />

                            <InputFloatingLabel
                                id="empresa"
                                type="text"
                                label="Empresa"
                                value={clienteFront.empresa}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, empresa: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'empresa')}
                            />

                            <InputFloatingLabel
                                id="fechaNacimiento"
                                type="date"
                                label="Fecha Nacimiento"
                                value={
                                    clienteFront.fechaNacimiento instanceof Date
                                        ? clienteFront.fechaNacimiento.toISOString().split('T')[0]
                                        : clienteFront.fechaNacimiento || ''
                                }
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, fechaNacimiento: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'fechaNacimiento')}
                            />
                            <InputFloatingLabel
                                id="casillero"
                                type="text"
                                label="Casillero"
                                value={clienteFront.casillero}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setClienteFront((prev) => ({ ...prev, casillero: e.target.value }));
                                }}
                                error={clienteFront.errores.find((error) => error.name == 'casillero')}
                            />
                        </CardContent>
                    </Card>

                    <Card className="flex h-auto w-[95%] max-w-[95%] flex-col p-0 md:w-[80%] md:max-w-[80%]">
                        <div className="w-[100%]">
                            <div id="HeaderTable" className="flex items-center justify-between p-4">
                                <span className="mr-2 font-semibold">Direcciones</span>

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
                                        variant="outline"
                                        onClick={() => {
                                            setConstruccionDireccion(consDirVacia);
                                            setAbrirModalDireccionRegistro(true);
                                        }}
                                    >
                                        <Plus /> Registrar Dirección
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
                                                                    onChange={(event) =>
                                                                        table.getColumn(header.id)?.setFilterValue(event.target.value)
                                                                    }
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
                                                        <TableCell key={cell.id}>
                                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                                        </TableCell>
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
                            </div>
                            <div className="flex items-center justify-end space-x-2 px-4 py-4">
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

                <Dialog
                    open={abrirModalDireccionRegistro}
                    onOpenChange={() => {
                        setAbrirModalDireccionRegistro((prev) => !prev);
                    }}
                >
                    <DialogContent className={'min-w-[600px]'}>
                        <DialogHeader>
                            <DialogTitle>{construccionDireccion.modoRegistro ? 'Registro' : 'Actualización'} Dirección</DialogTitle>
                            <DialogDescription>
                                Inserta los datos necesarios para {construccionDireccion.modoRegistro ? 'el registro' : 'la actualización'} de una
                                dirección.
                            </DialogDescription>
                        </DialogHeader>

                        <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Provincia
                                </Label>
                                <Select
                                    onValueChange={(value) => {
                                        CambiarProvincia(value, setConstruccionDireccion, setCantones, setDistritos);
                                    }}
                                    value={String(construccionDireccion.idProvincia)}
                                >
                                    <SelectTrigger className="w-[100%]">
                                        <SelectValue placeholder="Selec. una provincia..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {provincias.map((provincia) => (
                                                <SelectItem value={String(provincia.id)}>{provincia.descripcion}</SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Cantón
                                </Label>
                                <Select
                                    onValueChange={(value) => {
                                        CambiarCanton(value, setConstruccionDireccion, setDistritos);
                                    }}
                                    disabled={cantones.length <= 0}
                                    value={String(construccionDireccion.idCanton)}
                                >
                                    <SelectTrigger
                                        className="w-[100%]"
                                        error={construccionDireccion.errores.find((error) => error.name == 'idCanton')}
                                    >
                                        <SelectValue placeholder="Selec. un cantón..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {cantones.map((canton) => (
                                                <SelectItem value={String(canton.id)}>{canton.descripcion}</SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Distrito
                                </Label>
                                <Select
                                    onValueChange={(value) => {
                                        CambiarDistrito(value, setConstruccionDireccion);
                                    }}
                                    disabled={distritos.length <= 0}
                                    value={String(construccionDireccion.idDistrito)}
                                >
                                    <SelectTrigger
                                        className="w-[100%]"
                                        error={construccionDireccion.errores.find((error) => error.name == 'idDistrito')}
                                    >
                                        <SelectValue placeholder="Selec. un distrito..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {distritos.map((distrito) => (
                                                <SelectItem value={String(distrito.id)}>{distrito.descripcion}</SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Código Postal
                                </Label>
                                <Input
                                    type="number"
                                    className={'w-[100%]'}
                                    placeholder="Seleccione un distrito"
                                    value={construccionDireccion.codigoPostal}
                                    readOnly={true}
                                    error={construccionDireccion.errores.find((error) => error.name == 'codigoPostal')}
                                />
                            </div>
                        </div>

                        <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2'}>
                                <Label className="px-2 text-gray-500" required>
                                    Dirección
                                </Label>
                                <Input
                                    type="text"
                                    className={'w-[100%]'}
                                    placeholder="Indique la dirección"
                                    value={construccionDireccion.direccion}
                                    error={construccionDireccion.errores.find((error) => error.name == 'direccion')}
                                    onChange={(e) =>
                                        setConstruccionDireccion((prev) => ({
                                            ...prev,
                                            direccion: e.target.value,
                                        }))
                                    }
                                />
                            </div>
                        </div>

                        <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Link Waze
                                </Label>
                                <Input
                                    type="text"
                                    className={'w-[100%]'}
                                    placeholder="Indique el link de Waze"
                                    value={construccionDireccion.linkWaze}
                                    error={construccionDireccion.errores.find((error) => error.name == 'linkWaze')}
                                    onChange={(e) =>
                                        setConstruccionDireccion((prev) => ({
                                            ...prev,
                                            linkWaze: e.target.value,
                                        }))
                                    }
                                />
                            </div>

                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                <Label className="px-2 text-gray-500" required>
                                    Tipo de Dirección
                                </Label>
                                <Select
                                    onValueChange={(value) => {
                                        setConstruccionDireccion((prev) => ({ ...prev, tipoDireccion: Number(value) }));
                                    }}
                                    value={String(construccionDireccion.tipoDireccion)}
                                >
                                    <SelectTrigger
                                        className="w-[100%]"
                                        error={construccionDireccion.errores.find((error) => error.name == 'tipoDireccion')}
                                    >
                                        <SelectValue placeholder="Selec. el tipo..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {tiposDirecciones.map((tipo) => (
                                                <SelectItem value={String(tipo.id)}>{tipo.descripcion}</SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                            <div className={'flex w-[100%] max-w-[100%] flex-col gap-2'}>
                                <Button
                                    className={construccionDireccion.modoRegistro ? 'bg-red-400 text-white' : 'bg-blue-400 text-white'}
                                    onClick={() => {
                                        if (construccionDireccion.modoRegistro)
                                            RegistrarDireccion(
                                                construccionDireccion,
                                                setConstruccionDireccion,
                                                direcciones,
                                                setDirecciones,
                                                setAbrirModalDireccionRegistro,
                                                provincias,
                                                cantones,
                                                tiposDirecciones,
                                                setCantones,
                                                setDistritos,
                                            );
                                        else
                                            ActualizarDireccion(
                                                construccionDireccion,
                                                setConstruccionDireccion,
                                                direcciones,
                                                setDirecciones,
                                                setAbrirModalDireccionRegistro,
                                                provincias,
                                                cantones,
                                                tiposDirecciones,
                                                setCantones,
                                                setDistritos,
                                            );
                                    }}
                                >
                                    {construccionDireccion.modoRegistro ? 'Registrar' : 'Actualizar'} Dirección
                                </Button>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>
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

async function CambiarProvincia(
    idSeleccionado: string,
    setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>,
    setCantones: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
    setDistritos: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
) {
    // 1. Cambiar el setConstruccion
    // 2. Limpiar los cantones y distritos
    // 2. Llamar los cantones por el idProvincia seleccionado
    // 3. Subirlos a setCantones

    // 1. Cambiar el setConstruccion
    const idProvincia: number = Number(idSeleccionado);
    setConstruccionDireccion((prev) => ({
        ...prev,
        idProvincia: idProvincia,
        idCanton: -1,
        idDistrito: -1,
        codigoPostal: 0,
        errores: [],
    }));

    // 2. Limpiar los cantones y distritos
    setCantones([]);
    setDistritos([]);

    // 3. Llamar los cantones por el idProvincia seleccionado
    // 4. Subirlos a setCantones
    try {
        setCantones(await obtenerCantones(idProvincia));
    } catch (e) {
        if (e instanceof AppError) {
            setConstruccionDireccion((prev) => ({
                ...prev,
                errores: [
                    {
                        name: 'idCanton',
                        message: 'Hubo un error al cargar los cantones',
                    },
                ],
            }));
        }
    }
}

async function CambiarCanton(
    idSeleccionado: string,
    setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>,
    setDistrito: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
) {
    // 1. Cambiar el setConstruccion
    // 2. Limpiar los distritos
    // 2. Llamar los distritos por el idCanton seleccionado
    // 3. Subirlos a setDistrito

    // 1. Cambiar el setConstruccion
    const idCanton: number = Number(idSeleccionado);
    setConstruccionDireccion((prev) => ({
        ...prev,
        idCanton: idCanton,
        idDistrito: -1,
        codigoPostal: 0,
        errores: [],
    }));

    // 2. Limpiar los distritos
    setDistrito([]);

    // 3. Llamar los distritos por el idCanton seleccionado
    // 4. Subirlos a setDistrito
    try {
        setDistrito(await obtenerDistritos(idCanton));
    } catch (e) {
        if (e instanceof AppError) {
            setConstruccionDireccion((prev) => ({
                ...prev,
                errores: [
                    {
                        name: 'idDistrito',
                        message: 'Hubo un error al cargar los distritos',
                    },
                ],
            }));
        }
    }
}

async function CambiarDistrito(idSeleccionado: string, setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>) {
    // 1. Cambiar el setConstruccion
    // 2. Llamar el codigo postal por el idDistrito seleccionado
    // 3. Subirlos a codigoPostal

    // 1. Cambiar el setConstruccion
    const idDistrito: number = Number(idSeleccionado);
    setConstruccionDireccion((prev) => ({
        ...prev,
        idDistrito: idDistrito,
        codigoPostal: 0,
        errores: [],
    }));

    // 2. Llamar el codigo postal por el idDistrito seleccionado
    // 3. Subirlos a codigoPostal
    try {
        const codigoPostal: number = await obtenerCodigoPostal(idDistrito);
        setConstruccionDireccion((prev) => ({
            ...prev,
            codigoPostal: codigoPostal,
        }));
    } catch (e) {
        if (e instanceof AppError) {
            setConstruccionDireccion((prev) => ({
                ...prev,
                errores: [
                    {
                        name: 'codigoPostal',
                        message: 'Hubo un error al cargar los distritos',
                    },
                ],
            }));
        }
    }
}

function RegistrarDireccion(
    construccionDireccion: ConstruccionCodigoPostal,
    setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>,
    direcciones: DireccionesTable[],
    setDirecciones: React.Dispatch<React.SetStateAction<DireccionesTable[]>>,
    setAbrirModalDireccionRegistro: React.Dispatch<React.SetStateAction<boolean>>,
    provincias: ComboBoxItem[],
    cantones: ComboBoxItem[],
    tiposDirecciones: ComboBoxItem[],
    setCantones: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
    setDistritos: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
) {
    // 1. Ver que todos los datos esten completos
    // 2. Si la direccion a guardar es principal, ver si ya no hay principales guardadas
    // 3. si hay errores o no, mantener abierto el modal para que el usuario lo vea
    // 4. Si no hay errores, agregarlo a setDirecciones
    // 4.1. Borrar t#do lo que este en el dialog.

    // 1. Ver que todos los datos esten completos
    let hayError: boolean = ValidarCamposDireccion(construccionDireccion, setConstruccionDireccion);

    // 2. Si la direccion a guardar es principal, ver si ya no hay principales guardadas

    if (construccionDireccion.tipoDireccion === 1) {
        for (const direccion of direcciones) {
            if (direccion.tipo === 1) {
                setConstruccionDireccion((prev) => ({
                    ...prev,
                    errores: [...(prev.errores || []), { name: 'tipoDireccion', message: 'Solo puede haber una dirección como principal' }],
                }));
                hayError = true;
                break;
            }
        }
    }

    // 3. si hay errores o no, mantener abierto el modal para que el usuario lo vea
    setAbrirModalDireccionRegistro(hayError);

    // 4. Si no hay errores, agregarlo a setDirecciones
    if (!hayError) {
        // -Obtener el idUnico
        let idUnico: number = -1;

        for (const direccion of direcciones) {
            if (direccion.id == idUnico) idUnico--;
        }

        // -Obtener el paisEstado (Provincia,Canton)
        const provinciaTexto = provincias.find((provincia) => provincia.id === construccionDireccion.idProvincia);
        const cantonTexto = cantones.find((canton) => canton.id === construccionDireccion.idCanton);
        const paisEstado = provinciaTexto?.descripcion + ', ' + cantonTexto?.descripcion;
        const tipoDireccion = tiposDirecciones.find((td) => td.id === construccionDireccion.tipoDireccion);

        const direccionTabla: DireccionesTable = {
            id: idUnico,
            direccion: construccionDireccion.direccion,
            tipo: construccionDireccion.tipoDireccion,
            idCliente: -1, //porque en este contexto no importa, ya que se sabe que es el mismo cliente
            codigoPostal: String(construccionDireccion.codigoPostal), //despues solo se pasa a int
            paisEstado: paisEstado,
            linkWaze: construccionDireccion.linkWaze,
            tipoStatus: {
                descripcion: tipoDireccion?.descripcion ?? 'N/A',
                colorClass:
                    tipoDireccion?.descripcion === 'PRINCIPAL'
                        ? 'bg-transparent border-green-400 text-green-400'
                        : 'bg-transparent border-blue-400 text-blue-400',
            },
            actions: [
                {
                    descripcion: 'Detalle',
                    icon: 'Edit',
                    route: '',
                    actionType: '',
                    isActive: true,
                },
                {
                    descripcion: 'Eliminar',
                    icon: 'Trash2',
                    route: '',
                    actionType: '',
                    isActive: true,
                },
            ],
        };

        setDirecciones((prev) => [...prev, direccionTabla]);
        // 4.1. Borrar t#do lo que este en el dialog.
        setConstruccionDireccion({
            id: -1,
            idProvincia: -1,
            idCanton: -1,
            idDistrito: -1,
            codigoPostal: 0,
            direccion: '',
            linkWaze: '',
            tipoDireccion: -1,
            errores: [],
            modoRegistro: true,
        });
        setCantones([]);
        setDistritos([]);
    }
}

async function DetalleDireccion(
    setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>,
    direccionTable: DireccionesTable,
    setAbrirModalDireccionRegistro: React.Dispatch<React.SetStateAction<boolean>>,
    setCantones: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
    setDistrito: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
) {
    // 1. Mostrar el dialog
    // 2. Poner en modo actualizar
    // 3. Poner los campos del paisEstado a los distintos campos de construccionDireccion

    // 1. Mostrar el dialog
    setAbrirModalDireccionRegistro(true);
    setDistrito([]);
    setCantones([]);

    // 2. Poner en modo actualizar
    setConstruccionDireccion((prev) => ({ ...prev, modoRegistro: false }));

    // 3. Poner los campos del paisEstado a los distintos campos de construccionDireccion
    const data = await obtenerProvinciaCantonDistrito(direccionTable.codigoPostal);
    const cantones: ComboBoxItem[] = await obtenerCantones(data.provinciaId);
    const distritos: ComboBoxItem[] = await obtenerDistritos(data.cantonId);

    setCantones(cantones);
    setDistrito(distritos);

    setTimeout(() => {
        setConstruccionDireccion({
            id: direccionTable.id,
            codigoPostal: direccionTable.codigoPostal,
            direccion: direccionTable.direccion,
            linkWaze: direccionTable.linkWaze,
            tipoDireccion: direccionTable.tipo,
            idProvincia: Number(data.provinciaId),
            idCanton: Number(data.cantonId),
            idDistrito: Number(data.distritoId),
            errores: [],
            modoRegistro: false,
        });
    }, 500);
}

function EliminarDireccion(direccionTable: DireccionesTable, setDireccionesTable: React.Dispatch<React.SetStateAction<DireccionesTable>>) {
    // 1. Elimino la direccion del arreglo
    setDireccionesTable((prev) => prev.filter((dir) => dir.id !== direccionTable.id));
}

function ActualizarDireccion(
    construccionDireccion: ConstruccionCodigoPostal,
    setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>,
    direcciones: DireccionesTable[],
    setDirecciones: React.Dispatch<React.SetStateAction<DireccionesTable[]>>,
    setAbrirModalDireccionRegistro: React.Dispatch<React.SetStateAction<boolean>>,
    provincias: ComboBoxItem[],
    cantones: ComboBoxItem[],
    tiposDirecciones: ComboBoxItem[],
    setCantones: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
    setDistritos: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
) {
    // 1. Ver que todos los datos esten completos
    // 2. Si la direccion a guardar es principal, ver si ya no hay principales guardadas exceptuando la propia
    // 3. si hay errores o no, mantener abierto el modal para que el usuario lo vea
    // 4. Si no hay errores, actualizarlo a setDirecciones
    // 4.1. Borrar t#do lo que este en el dialog.

    // 1. Ver que todos los datos esten completos
    let hayError: boolean = ValidarCamposDireccion(construccionDireccion, setConstruccionDireccion);

    // 2. Si la direccion a guardar es principal, ver si ya no hay principales guardadas
    if (construccionDireccion.tipoDireccion === 1) {
        for (const direccion of direcciones) {
            if (direccion.tipo === 1 && direccion.id !== construccionDireccion.id) {
                setConstruccionDireccion((prev) => ({
                    ...prev,
                    errores: [...(prev.errores || []), { name: 'tipoDireccion', message: 'Solo puede haber una dirección como principal' }],
                }));
                hayError = true;
                break;
            }
        }
    }

    // 3. si hay errores o no, mantener abierto el modal para que el usuario lo vea
    setAbrirModalDireccionRegistro(hayError);

    // 4. Si no hay errores, actualizarlo a setDirecciones
    if (!hayError) {
        // -Obtener el paisEstado (Provincia, Canton)
        const provinciaTexto = provincias.find((provincia) => provincia.id === construccionDireccion.idProvincia);
        const cantonTexto = cantones.find((canton) => canton.id === construccionDireccion.idCanton);
        const paisEstado = provinciaTexto?.descripcion + ', ' + cantonTexto?.descripcion;
        const tipoDireccion = tiposDirecciones.find((td) => td.id === construccionDireccion.tipoDireccion);

        const direccionTabla: DireccionesTable = {
            id: construccionDireccion.id,
            direccion: construccionDireccion.direccion,
            tipo: construccionDireccion.tipoDireccion,
            idCliente: -1, //porque en este contexto no importa, ya que se sabe que es el mismo cliente
            codigoPostal: String(construccionDireccion.codigoPostal), //despues solo se pasa a int
            paisEstado: paisEstado,
            linkWaze: construccionDireccion.linkWaze,
            tipoStatus: {
                descripcion: tipoDireccion?.descripcion ?? 'N/A',
                colorClass:
                    tipoDireccion?.descripcion === 'PRINCIPAL'
                        ? 'bg-transparent border-green-400 text-green-400'
                        : 'bg-transparent border-blue-400 text-blue-400',
            },
            actions: [
                {
                    descripcion: 'Detalle',
                    icon: 'Edit',
                    route: '',
                    actionType: '',
                    isActive: true,
                },
                {
                    descripcion: 'Eliminar',
                    icon: 'Trash2',
                    route: '',
                    actionType: '',
                    isActive: true,
                },
            ],
        };

        setDirecciones((prev) => prev.map((direccion) => (direccion.id === construccionDireccion.id ? direccionTabla : direccion)));
        // 4.1. Borrar t#do lo que este en el dialog.
        setConstruccionDireccion({
            id: -1,
            idProvincia: -1,
            idCanton: -1,
            idDistrito: -1,
            codigoPostal: 0,
            direccion: '',
            linkWaze: '',
            tipoDireccion: -1,
            errores: [],
            modoRegistro: true,
        });
        setCantones([]);
        setDistritos([]);
    }
}
