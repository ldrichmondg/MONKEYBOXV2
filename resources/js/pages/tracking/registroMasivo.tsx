import AppLayout from '@/layouts/app-layout';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import { Head, Link } from '@inertiajs/react';

import { PageProps } from '@inertiajs/core';
import { usePage } from '@inertiajs/react';

import { Card } from '@/components/ui/card';
import { Combobox } from '@/ownComponents/combobox';

import TagInput from '@/ownComponents/tagInput';
import { Import, MoreHorizontal, MoveRight, RefreshCcw } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Spinner } from '@/ownComponents/spinner';
import { TagifyTag } from '@/types';
import React, { useEffect, useState } from 'react';

//api calls
import { obtenerConfiguracion } from '@/api/configuracion/configuracion';
import { comboboxProveedor } from '@/api/proveedor/proveedor';
//Tabla
import { ColumnDef, flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';

import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { iconMap } from '@/lib/iconMap';
import { EliminarModal } from '@/ownComponents/modals/eliminarModal';
import { LucideIcon } from 'lucide-react';

// /////////////// Tipos
import { obtenerEstatusMBox } from '@/api/estatus/estatusMBox';
import { PrealertarTracking } from '@/api/tracking/prealertarTracking';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { type BreadcrumbItem, ButtonHeader, ComboBoxItem } from '@/types';
import { Configuracion } from '@/types/configuracion';
import { EstatusTable, WithActions } from '@/types/table';
import { TrackingConPrealertaBaseProveedor, TrackingConsultadosTable } from '@/types/tracking';
import { InputError } from '@/types/input';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consulta Trackings',
        href: route('tracking.consulta.vista'),
    },
    {
        title: 'Registrar Masivo',
        href: route('tracking.registroMasivo.vista'),
    },
];

export const columns: ColumnDef<TrackingConsultadosTable>[] = [
    {
        accessorKey: 'idTracking',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Tracking ID
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('idTracking')}</div>,
    },
    {
        accessorKey: 'trackingProveedor',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Tracking Proveedor
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('trackingProveedor')}</div>,
    },
    {
        accessorKey: 'nombreCliente',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Cliente
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('nombreCliente')}</div>,
    },
    {
        accessorKey: 'descripcion',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Descripción
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('descripcion')}</div>,
    },
    {
        accessorKey: 'valor',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Valor
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('valor')}</div>,
    },
    {
        accessorKey: 'desde',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Desde
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('desde')}</div>,
    },
    {
        accessorKey: 'hasta',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Hasta
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('hasta')}</div>,
    },
    {
        accessorKey: 'couriers',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Courier/s
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('couriers')}</div>,
    },
    {
        accessorKey: 'nombreProveedor',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Proveedor
                </Button>
            );
        },
        cell: ({ row }) => <div className="capitalize">{row.getValue('nombreProveedor')}</div>,
    },
    {
        accessorKey: 'estatus',
        header: ({ column }) => {
            return (
                <Button variant="ghost" className="text-gray-500" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Estatus
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
                            const LucideIcon: LucideIcon = iconMap[action.icon ?? 'UserPlus'];
                            const needsModal = action.actionMessage != undefined && action.actionModalTitle != undefined;
                            return (
                                <DropdownMenuItem
                                    key={object.id}
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

type CardData = {
    idCliente: number;
    nombreCliente: string;
    trackings: string[];
};

interface Props extends PageProps {
    clientes: ComboBoxItem[];
}

function CardClienteTracking({
    clientes = [],
    onChange,
    cardCliente,
    isActive,
}: {
    clientes: ComboBoxItem[];
    onChange: (newData: CardData) => void;
    cardCliente: CardData;
    isActive: boolean;
}): React.ReactElement {
    const [tags, setTags] = useState<TagifyTag[]>([]);
    const handleCambioCliente = (idSeleccionado: number) => {
        const cliente = clientes.find((c) => c.id === idSeleccionado);
        if (cliente) {
            onChange({
                trackings: cardCliente.trackings,
                idCliente: idSeleccionado,
                nombreCliente: cliente.descripcion,
            });
        }
    };

    function handleCambioTrackings(): void {
        const tagsString: string[] = [];
        tags.forEach((tag) => {
            tagsString.push(tag.value);
        });

        if (!arraysSonIguales(tagsString, cardCliente.trackings)) {
            cardCliente.trackings = tagsString;
            onChange({
                trackings: tagsString,
                idCliente: cardCliente.idCliente,
                nombreCliente: cardCliente.nombreCliente,
            });
        }
    }

    useEffect(handleCambioTrackings, [cardCliente, tags]);

    return (
        <Card className="my-3 flex items-center justify-between p-4">
            <Combobox
                items={clientes}
                placeholder="Seleccione el cliente..."
                classNames="w-60 p-6"
                onChange={handleCambioCliente}
                isActive={isActive}
            ></Combobox>

            <MoveRight className="size-12 text-gray-500" />
            <TagInput
                value={tags}
                className="w-190 min-w-[300px] rounded-md p-8"
                placeholder="Digite los trackings"
                onChange={setTags}
                settings={{
                    placeholder: 'Escribe y presiona enter',
                    dropdown: {
                        enabled: 1, // activar dropdown de sugerencias
                    },
                }}
                isActive={isActive}
            />
        </Card>
    );
}

export default function RegistroMasivo() {
    const { props } = usePage<Props>();
    const { clientes } = props;
    const [cards, setCards] = useState<CardData[]>([]);
    const [btnBuscarActive, setBtnBuscarActive] = useState(true); //el valor real es false
    const [loading, setLoading] = useState(false); //debe estar como false
    const [showSpinner, setShowSpinner] = useState(false);
    const [proveedores, setProveedores] = useState<ComboBoxItem[]>([]);
    const [preealertando, setPreealertando] = useState(false);
    const [puedePreealertar, setPuedePreealertar] = useState(false);
    const [trackings, setTrackings] = useState<TrackingConsultadosTable[]>([]);

    //importar los datos de proveedor. Se hace asi porque await no lo permite dentro de esta fn
    useEffect(() => {
        cargarProveedores(setProveedores);
    }, []);

    const agregarCard = () => {
        setCards((prev) => [...prev, { idCliente: -1, trackings: [], nombreCliente: '' }]);
        setBtnBuscarActive(CamposLlenos(cards));
    };

    const actualizarCard = (
        index: number,
        newData: {
            idCliente: number;
            nombreCliente: string;
            trackings: string[];
        },
    ) => {
        setCards((prev) => prev.map((card, i) => (i === index ? newData : card)));
        const nuevosCards = cards.map((card, i) => (i === index ? newData : card));
        setCards(nuevosCards);

        setBtnBuscarActive(CamposLlenos(nuevosCards));
    };

    //useEffect parra puedePrealertar
    useEffect(() => {
        VerificarPuedePreealertar(setPuedePreealertar, trackings);
    }, [trackings]);

    //Todo lo relacionado a la tabla
    const table = useReactTable({
        data: trackings,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    function ActualizarTracking(e: React.ChangeEvent<HTMLInputElement>, idTracking: string, campo: string) {
        //campo se usa para poner si es valor
        let valor: number;
        let descripcion: string;
        if (campo == 'descripcion') descripcion = e.target.value;
        else if (campo == 'valor') valor = Number(e.target.value);

        setTrackings((prev) =>
            prev.map((tracking) =>
                tracking.idTracking === idTracking
                    ? {
                          ...tracking,
                          descripcion: campo == 'descripcion' ? descripcion : tracking.descripcion,
                          valor: campo == 'valor' ? valor : tracking.valor,
                      }
                    : tracking,
            ),
        );
    }

    //donde se va a enviar el formulario
    async function EnviarFormulario(): Promise<void> {
        //1. Insertar Datos en la tabla
        //2. hacer el request de ParcelsApp
        try {
            const trackingsNuevos: TrackingConsultadosTable[] = await InsertarDatosTabla(
                setBtnBuscarActive,
                setShowSpinner,
                setTrackings,
                setLoading,
                cards,
            ); //se crea porque pasar trackings de golpe react tiene la lista vacia

            await RegistrarTrackings(trackingsNuevos, setTrackings);
        } catch (error) {
            console.error('Error al enviar formulario:', error);
        }
    }

    const buttons: ButtonHeader[] = [
        {
            id: 'nuevoCliente',
            name: 'Nuevo Cliente',
            className: 'bg-red-400 text-white hover:bg-red-500 ',
            isActive: !loading,
            onClick: agregarCard,
        },
        {
            id: 'buscarTrackings',
            name: 'Buscar Trackings',
            className: 'bg-orange-400 text-white hover:bg-orange-300 ',
            isActive: btnBuscarActive,
            onClick: EnviarFormulario,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title="Registro" />
            <MainContainer>
                {cards.map((cardData, index) => (
                    <CardClienteTracking
                        key={index}
                        cardCliente={cardData}
                        onChange={(newData) => actualizarCard(index, newData)} //(newData) es lo que pasa el componente hijo para despues llamar a la fn actualizarCard
                        clientes={clientes}
                        isActive={!loading}
                    />
                ))}

                <Spinner isActive={showSpinner}></Spinner>
                {/* Esto ocurre cuando se buscan los trackings */}
                {loading && (
                    <Card className="w-[95%] p-0 md:my-3 lg:my-5">
                        <div className="w-[100%]">
                            <div id="HeaderTable" className="flex items-center justify-between p-4">
                                <span className="mr-2 font-semibold">Trackings Consultados</span>

                                <div className="flex flex-1 items-center justify-end">
                                    <Button
                                        className={'flex items-center border border-gray-100 bg-transparent text-base text-gray-500'}
                                        variant="outline"
                                        size="lg"
                                        disabled={!puedePreealertar || preealertando}
                                        onClick={() => PreealertarTracking(trackings, setTrackings, setPreealertando)}
                                    >
                                        {!preealertando ? (
                                            <>
                                                {' '}
                                                <Import className="!h-5 !w-5" /> Preealertar{' '}
                                            </>
                                        ) : (
                                            <>
                                                {' '}
                                                <RefreshCcw className="animate-spin" /> Preealertando{' '}
                                            </>
                                        )}
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
                                                            {cell.column.id == 'valor' || cell.column.id == 'descripcion' ? (
                                                                <Input
                                                                    onChange={(e) => ActualizarTracking(e, row.original.idTracking, cell.column.id)}
                                                                    className="max-w-sm"
                                                                    type={cell.column.id == 'valor' ? 'number' : 'text'}
                                                                    value={
                                                                        cell.column.id == 'valor'
                                                                            ? row.original.valor
                                                                            : (row.original.descripcion ?? '')
                                                                    }
                                                                    disabled={preealertando}
                                                                    error={row.original.errores.find(
                                                                        (error) => error.name == cell.column.id + '-' + row.original.idTracking,
                                                                    )}
                                                                />
                                                            ) : cell.column.id == 'nombreProveedor' ? (
                                                                <Combobox
                                                                    items={proveedores}
                                                                    placeholder="Selec. proveedor..."
                                                                    classNames="w-60 p-6"
                                                                    onChange={(idSeleccionado) =>
                                                                        AgregarProveedor(idSeleccionado, proveedores, row.original, setTrackings)
                                                                    }
                                                                    isActive={row.original.trackingCompleto == true && !preealertando}
                                                                    error={row.original.errores.find(
                                                                        (error) => error.name == 'idProveedor-' + row.original.idTracking,
                                                                    )}
                                                                ></Combobox>
                                                            ) : (
                                                                flexRender(cell.column.columnDef.cell, cell.getContext())
                                                            )}
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
                                        Anterior
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                                        Siguiente
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </Card>
                )}
            </MainContainer>
        </AppLayout>
    );
}

function CamposLlenos(cards: CardData[]) {
    let camposLlenos = true;

    cards.forEach((card) => {
        if (card.idCliente == -1 || card.trackings.length == 0) {
            camposLlenos = false;
        }
    });

    return camposLlenos;
}

function arraysSonIguales(a: string[], b: string[]): boolean {
    if (a.length !== b.length) return false;
    for (let i = 0; i < a.length; i++) {
        if (a[i] !== b[i]) return false;
    }
    return true;
}

// FORMULARIO

// #########
// FUNCION PARA INSERTAR DATOS EN LA TABLA
// #########
async function InsertarDatosTabla(
    setBtnBuscarActive: React.Dispatch<React.SetStateAction<boolean>>,
    setShowSpinner: React.Dispatch<React.SetStateAction<boolean>>,
    setTrackings: React.Dispatch<React.SetStateAction<TrackingConsultadosTable[]>>,
    setLoading: React.Dispatch<React.SetStateAction<boolean>>,
    cards: CardData[],
): Promise<TrackingConsultadosTable[]> {
    //0. Mostrar el spinner
    //1. Hacer el request del archivo de configuracion para obtener el valor
    //2. Pasar la información de los cards a objetos tracking
    //3. la lista de objetos tracking pasarla a la tabla data (esto ya se hace)
    //4. mostrar la tabla y quitar el spinner

    try {
        const trackings: TrackingConsultadosTable[] = [];
        //0. Mostrar el spinner
        setBtnBuscarActive(false); //es para que se desactive el btn de buscar trackings
        setShowSpinner(true);

        //1. Hacer el request del archivo de configuracion para obtener el valor
        const configuracion: Configuracion | null = await obtenerConfiguracion();
        let estadoSinRegistrar: EstatusTable[] | null = await obtenerEstatusMBox(['Sin Registrar']);

        if (configuracion == null) {
            ErrorModal('Error al cargar la configuración', 'No se cargo el campo valor para ponerlo en la prealerta');
            return [];
        }

        if (estadoSinRegistrar == null) {
            /* Si el estado no se carga, entonces se usa el que se sabe */
            estadoSinRegistrar = [
                {
                    descripcion: 'Sin Preealertar',
                    colorClass: 'bg-transparent border-pink-300 text-pink-300',
                },
            ];
            return [];
        }

        //2. Pasar la información de los cards a objetos tracking
        cards.forEach((card) => {
            //como trackings es un arreglo con los idTracking
            card.trackings.forEach((idTracking) => {
                const tracking: TrackingConsultadosTable = {
                    idTracking: idTracking,
                    idCliente: card.idCliente,
                    id: -1,
                    nombreCliente: card.nombreCliente,
                    descripcion: '',
                    couriers: '',
                    trackingProveedor: '',
                    valor: configuracion.valor,
                    proveedor: '',
                    estatus: estadoSinRegistrar[0],
                    desde: null,
                    hasta: null,
                    destino: null,
                    errores: [
                        {
                            name: 'descripcion-' + idTracking,
                        },
                        {
                            name: 'valor-' + idTracking,
                        },
                        {
                            name: 'idProveedor-' + idTracking,
                        },
                    ],
                    actions: [],
                };
                setTrackings((prev) => prev.concat(tracking));
                trackings.push(tracking);
            });
        });

        //4. Mostrar la tabla y quitar el spinner
        setShowSpinner(false);
        setLoading(true);

        return trackings;
    } catch (e) {
        console.log('[registroMasivo->IDT] error: ' + e);
        return [];
    }
}

async function RegistrarTrackings(
    trackings: TrackingConsultadosTable[],
    setTrackings: React.Dispatch<React.SetStateAction<TrackingConsultadosTable[]>>,
) {
    //1. Recorrer cada tracking para registrarlo
    //2. Cuando entra a un tracking, cambiar el estado a registrando... y ponerlo en negro
    //3. Hacer el request para registrar el trackingId
    //4. Cuando se obtiene la respuesta se recibe un objeto de tipo TrackingConsultadosTable con el estado en PreAlertado o No se encontro
    //4.1. Se consulta por idTracking y se reemplaza
    //5. Poner/Mostrar el boton de preAlertar
    try {
        for (const t of trackings) {
            //2. Cuando entra a un tracking, cambiar el estado a registrando... y ponerlo en negro
            setTrackings((prev) =>
                prev.map((item) =>
                    item.idTracking === t.idTracking
                        ? {
                              ...item,
                              estatus: {
                                  descripcion: 'Registrando...',
                                  colorClass: 'bg-transparent border-brown-400 text-brown-400',
                              },
                          }
                        : item,
                ),
            );

            //3. Hacer el request para registrar el tracking
            try {
                const response = await fetch(route('usuario.tracking.registro.guardar'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json', //esto es para que no hara redirecciones automaticas y me indique el response
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        idTracking: t.idTracking,
                        idCliente: t.idCliente,
                    }),
                });

                if (!response.ok) {
                    ErrorModal('Error al registrar los trackings', 'Hubo un error al registrar los trackings');
                    throw new Error('Error al registrar los trackings.');
                }

                //4. Cuando se obtiene la respuesta se recibe un objeto de tipo TrackingConsultadosTable con el estado en PreAlertado o No se encontro
                // - El response trae la propiedad 'trackingCompleto'
                const responseTracking: TrackingConsultadosTable = await response.json();

                // -Si no se encontro
                if (!responseTracking.trackingCompleto) {
                    setTrackings((prev) =>
                        prev.map((item) =>
                            item.idTracking === t.idTracking
                                ? {
                                      ...item,
                                      estatus: {
                                          descripcion: 'No se encontró',
                                          colorClass: 'bg-transparent border-red-400 text-red-400',
                                      },
                                  }
                                : item,
                        ),
                    );
                } else {
                    setTrackings((prev) =>
                        prev.map((item) =>
                            item.idTracking === t.idTracking
                                ? {
                                      ...item,
                                      id: responseTracking.id,
                                      desde: responseTracking.desde,
                                      hasta: responseTracking.hasta,
                                      destino: responseTracking.destino,
                                      couriers: responseTracking.couriers,
                                      idCliente: responseTracking.idCliente,
                                      nombreCliente: responseTracking.nombreCliente,
                                      estatus: {
                                          descripcion: responseTracking.estatus.descripcion,
                                          colorClass: responseTracking.estatus.colorClass,
                                      },
                                      actions: responseTracking.actions,
                                      trackingCompleto: responseTracking.trackingCompleto,
                                  }
                                : item,
                        ),
                    );
                }
            } catch (error) {
                console.error('Error de red:', error);
                setTrackings((prev) =>
                    prev.map((item) =>
                        item.id === t.id
                            ? {
                                  ...item,
                                  estatus: {
                                      descripcion: 'Error ❌',
                                      colorClass: 'bg-yellow-100 text-yellow-800',
                                  },
                              }
                            : item,
                    ),
                );
            }
        }
    } catch (error) {
        console.error('[registroMasivo,RegistrarTrackings] error: ', error);
    }
}

// #############################
// FUNCION PARA VERIFICAR SI SE PUEDE PREALERTAR
// #############################

function AgregarProveedor(
    idProveedor: number,
    proveedores: ComboBoxItem[],
    trackingSeleccionado: TrackingConsultadosTable,
    setTrackings: React.Dispatch<React.SetStateAction<TrackingConsultadosTable[]>>,
) {
    // 1. Se ingresa el idProveedor y nombreProveedor al tracking
    // 2. Se verifica si todos los proveedores que estan completos tienen proveedor
    // 2.1. Si todos estan con proveedor entonces se pasa setPuedePreealertar a true
    try {
        // 1. Se ingresa el idProveedor y nombreProveedor al tracking
        setTrackings((prev) =>
            prev.map((tracking) =>
                trackingSeleccionado.idTracking == tracking.idTracking
                    ? {
                          ...tracking,
                          idProveedor: idProveedor,
                          nombreProveedor: proveedores.map((prov) => prov.id == idProveedor).descripcion,
                      }
                    : tracking,
            ),
        );

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al Agregar Proveedor', 'Lo sentimos, hubo un error de chequeo de proveedores. Comunicate con el departamento de TI');
    }
}

// ###########
// CARGAR PROVEEDORES
// ###########

async function cargarProveedores(setProveedores: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>) {
    const proveedores: ComboBoxItem[] = await comboboxProveedor();
    setProveedores(proveedores);
}

// ###################################
// VERIFICAR SI PUEDE PREEALERTAR
// ###################################
function VerificarPuedePreealertar(setPuedePreealertar: React.Dispatch<React.SetStateAction<boolean>>, trackings: TrackingConsultadosTable[]) {
    // 2. Se verifica si todos los proveedores que estan completos tienen proveedor
    // 2.1. Si todos estan con proveedor entonces se pasa setPuedePreealertar a true

    try {
        const cantidadTrackingsCompletos = trackings.filter((tracking) => tracking.trackingCompleto == true).length;
        const cantidadTrackingsParaPreealertar = trackings.filter(
            (tracking) => tracking.idProveedor != null && tracking.valor !== -1 && tracking.descripcion !== '',
        ).length;

        const puedePreealertar: boolean = cantidadTrackingsCompletos == cantidadTrackingsParaPreealertar && cantidadTrackingsParaPreealertar != 0;

        setPuedePreealertar(puedePreealertar);

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al verificar proveedores', 'Lo sentimos, hubo un error de chequeo de proveedores. Comunicate con el departamento de TI');
    }
}

// #################################
// TODO LO NECESARIO VISUALMENTE PARA ENSENARLE AL USUARIO QUE SE ESTA PREALERTANDO
// #################################

async function PreealertarTracking(
    trackings: TrackingConsultadosTable[],
    setTrackings: React.Dispatch<React.SetStateAction<TrackingConsultadosTable[]>>,
    setPreealertando: React.Dispatch<React.SetStateAction<boolean>>,
) {
    // 1. Se va a poner la variable en preealertando, para indicar que se esta prealertando
    // 2. Se va a recorrer cada tracking
    // 2.1. Se va a cambiar el estado de sin preealertar a preealertando
    // 2.2  Se envia un request de ese tracking para hacerle la prealerta
    // 3. Si se logro la preealerta, se pone el estado como preealertado y se pone el T.Prov (por el momento si es de aeropost)
    // 3.1 Si no se logra, poner en estado no se logro prealerta ( => su estado sigue siendo sin preealertado)
    // 4.
    try {
        // 1. Se va a poner la variable en preealertando, para indicar que se esta prealertando
        setPreealertando(true);

        // 2. Se va a recorrer cada tracking
        for (const trackingFor of trackings) {
            //esto es para que se pueda ir secuencial poniendo los estados
            // 2.1. Se va a cambiar el estado de sin preealertar a preealertando
            setTrackings((prev) =>
                prev.map((tracking) =>
                    trackingFor.idTracking == tracking.idTracking && tracking.trackingCompleto
                        ? {
                              ...tracking,
                              estatus: {
                                  descripcion: 'preealertando...',
                                  colorClass: 'bg-transparent border-gray-400 text-gray-400',
                              },
                          }
                        : tracking,
                ),
            );

            // 2.2  Se envia un request de ese tracking para hacerle la prealerta
            const trackingParaPrealertar: TrackingConPrealertaBaseProveedor = {
                ...trackingFor,
                id: trackingFor.id,
                idTracking: trackingFor.idTracking,
                nombreCliente: trackingFor.nombreCliente,
                descripcion: trackingFor.descripcion,
                proveedor: trackingFor.trackingProveedor,
                idProveedor: trackingFor.idProveedor ?? -1,
                trackingProveedor: trackingFor.trackingProveedor,
                prealerta: {
                    id: -1 /* aun no se sabe porque no se ha prealertado aun */,
                    valor: trackingFor.valor,
                    descripcion: trackingFor.descripcion,
                    idTrackingProveedor: -1 /* aun no se sabe porque no se ha prealertado aun */,
                },
                errores: trackingFor.errores,
                estatus: trackingFor.estatus,
            };

            /* Un try por si ocurre un problema al recibir el tracking prealertado */
            try {
                const trackingRespuesta: TrackingConPrealertaBaseProveedor | null = await PrealertarTracking(trackingParaPrealertar);

                // 3.1 Si no se logra, poner en estado no se logro prealerta ( => su estado sigue siendo sin preealertado)
                if (!trackingRespuesta) {
                    throw new Error('La API devolvió null en lugar de un tracking válido');
                }

                // 3. Si se logro la preealerta, se pone el estado como preealertado y se pone el T.Prov (por el momento si es de aeropost)
                // - Solo para sincronizar los errores
                setTrackings((prev) =>
                    prev.map((tracking) => {
                        if (trackingParaPrealertar.idTracking === tracking.idTracking) {
                            // Creamos un nuevo array de errores, actualizando mensajes donde corresponda
                            const erroresActualizados: InputError[] = tracking.errores.map((errorActual) => {
                                // Buscamos en trackingRespuesta un error cuyo name sea prefijo del errorActual.name
                                const errorRespuesta: InputError | undefined = trackingRespuesta.errores.find((errResp) =>
                                    errorActual.name.startsWith(errResp.name),
                                );

                                if (errorRespuesta) {
                                    return {
                                        ...errorActual,
                                        message: errorRespuesta.message, // Actualizamos el mensaje con el del response
                                    };
                                }

                                // Si no coincide, retornamos el errorActual sin cambio
                                return errorActual;
                            });

                            return {
                                ...tracking,
                                errores: erroresActualizados,
                                estatus: {
                                    descripcion: trackingRespuesta.estatus.descripcion,
                                    colorClass: trackingRespuesta.estatus.colorClass,
                                },
                            };
                        }
                        return tracking;
                    }),
                );

                // - Verificar si hubo un error de validacion de idTracking
                const errorIdTracking = trackingRespuesta.errores.find((error) => error.name == 'idTracking');
                if (errorIdTracking) {
                    ErrorModal(
                        'Error al subir la prealerta',
                        'Lo sentimos hubo un error al subir la prealerta. Indica el siguiente mensaje al departamento de TI: No se enlazó idTracking',
                    );
                }

                // eslint-disable-next-line @typescript-eslint/no-unused-vars
            } catch (e) {
                ErrorModal(
                    'No se obtuvo respuesta del tracking que se prealertó',
                    'No se obtuvo respuesta del tracking a prealertar. Intente de nuevo o comuniquese con el departamento de TI.',
                );
            }
            /*
             trackingFor.idTracking == tracking.idTracking && tracking.trackingCompleto
                        ? {
                              ...tracking,
                              estatus: {
                                  descripcion: 'preealertando...',
                                  colorClass: 'bg-transparent border-gray-400 text-gray-400',
                              },

                          }
                        : tracking,
             */
        }

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        console.log('[RegistroMasivo, PT] error:' + e);
        ErrorModal('Error al preealertar trackings', 'Hubo un error al intentar preealertar los trackings. Por favor contactar con soporte TI');
    }
}
