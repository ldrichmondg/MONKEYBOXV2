import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import Cubes from '@/ownComponents/cubes';
import InputFloatingLabel from '@/ownComponents/inputFloatingLabels';
import { WhatsappIcon } from '@/ownComponents/svgs/whatsApp';

import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Combobox } from '@/ownComponents/combobox';

import { Toaster } from '@/components/ui/sonner';
import { type BreadcrumbItem, ButtonHeader, ComboBoxItem } from '@/types';
import { TrackingCompleto } from '@/types/tracking';
import { Head, Link } from '@inertiajs/react';
import { Box, Calendar, CircleCheck, CircleX, Clock4, FileDown, LucideIcon, MoreHorizontal, MoveRight, Plus, RotateCcw, Trash } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

//api calls
import { ActualizarTracking, SincronizarCambios, SubirFactura } from '@/api/tracking/detalleTracking';
import { iconMap } from '@/lib/iconMap';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { comboboxDirecciones } from '@/servicesFront/direccion/servicioFrontDireccion';
import { cargarProveedores } from '@/servicesFront/proveedor/servicioFrontProveedores';
import { HistorialTracking } from '@/types/historialTracking';

//importar archivo auxiliar
import { ObtenerCliente } from '@/api/clientes/cliente';
import { EliminarModal } from '@/ownComponents/modals/eliminarModal';
import { Spinner } from '@/ownComponents/spinner';
import {
    AccionEliminar,
    AccionEN,
    AccionOMB,
    AccionPA, AccionPaquetePerdido,
    AccionPreartar,
    AccionRMI,
    AccionSinPrealertar,
    AccionTCR
} from '@/pages/tracking/detalle/detalleTrackingFuncionesAux';
import { ClienteCompleto, ClienteTracking } from '@/types/cliente';
import { Imagen } from '@/types/imagenes';
import { ExitoModal } from '@/ownComponents/modals/exitoModal';

interface Props {
    tracking: TrackingCompleto;
    clientes: ComboBoxItem[];
    direcciones: ComboBoxItem[];
}

interface MensajeDialog {
    descripcion: string;
    titulo: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consulta Trackings',
        href: route('tracking.consulta.vista'),
    },
    {
        title: 'Detalle',
        href: route('tracking.registroMasivo.vista'),
    },
];

export default function DetalleTracking({ tracking, clientes, direcciones }: Props) {
    const [proveedores, setProveedores] = useState<ComboBoxItem[]>([]);
    const [direccionesFront, setDireccionesFront] = useState<ComboBoxItem[]>(direcciones);
    const [trackingFront, setTracking] = useState<TrackingCompleto>({ ...tracking, errores: [] });
    const [historialesBocetos, setHistorialesBocetos] = useState<HistorialTracking[]>([]);
    const [mensajeDialog, setMensajeDialog] = useState<MensajeDialog>(null);

    //variables de consulta:
    const [cargandoDirecciones, setCargandoDirecciones] = useState<boolean>(false);
    const [mostrarDialog, setMostrarDialog] = useState<boolean>(false);

    //variables de archivos
    const fileInputRef = useRef<HTMLInputElement | null>(null);

    //variables de actualizar
    const [actualizando, setActualizando] = useState<boolean>(false);
    const [guardandoFactura, setGuardandoFactura] = useState<boolean>(false);

    //variables de header
    const buttons: ButtonHeader[] = [
        {
            id: 'sincronizarCambios',
            name: 'Sincronizar Cambios',
            className: 'bg-orange-400 text-white hover:bg-orange-500 ',
            isActive: true,
            onClick: () => SincronizarCambiosAux(trackingFront, setTracking, setActualizando),
            icon: RotateCcw,
        },
        {
            id: 'contactarCliente',
            name: 'Contactar Cliente',
            className: 'bg-green-400 text-white hover:bg-green-300 ',
            isActive: !cargandoDirecciones,
            onClick: () => ContactarCliente(trackingFront),
            icon: WhatsappIcon,
        },
        {
            id: 'guardarCambios',
            name: 'Guardar cambios',
            className: 'bg-red-400 text-white hover:bg-red-300 ',
            isActive: true,
            onClick: () => ActualizarTrackingAux(trackingFront, setActualizando),
        },
    ];

    console.log(tracking);

    useEffect(() => {
        cargarProveedores(setProveedores);
    }, []);

    useEffect(() => {
        console.log(trackingFront);
    }, [trackingFront]);

    useEffect(() => {
        //console.log(historialesBocetos);
    }, [historialesBocetos]);

    useEffect(() => {
        //console.log(mostrarDialog);
    }, [mostrarDialog]);

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title={trackingFront.idTracking} />
            <MainContainer>
                <div className="flex w-full gap-4 pt-3">
                    <Card className="flex w-[25%] max-w-[55%] flex-col px-[0.9rem] py-3 xl:w-[55%] xl:max-w-[25%]">
                        <CardHeader className={'w-[100%] p-0'}>
                            <div className={'flex justify-between'}>
                                <div className="flex size-9 items-center justify-center rounded-sm bg-red-400 px-2 text-sidebar-primary-foreground">
                                    <Box className="size-7 dark:text-black" />
                                </div>

                                <Button className="bg-orange-400 px-5 text-sm text-white hover:bg-orange-500"> Hijos: 1 de 1</Button>
                            </div>
                        </CardHeader>

                        <CardContent className={'flex flex-col p-0'}>
                            <p className="font-bold text-gray-500"> {trackingFront.idTracking}</p>
                            {trackingFront.idProveedor == 2 ? (
                                <Input
                                    id="trackingProveedor"
                                    className="text-bold"
                                    placeholder="Indique el #proveedor..."
                                    value={trackingFront.trackingProveedor}
                                    onChange={(e) =>
                                        setTracking((prev) => ({
                                            ...prev,
                                            trackingProveedor: e.target.value,
                                        }))
                                    }
                                />
                            ) : (
                                <p className="text-bold"> {trackingFront.trackingProveedor} </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="flex w-[75%] max-w-[75%] flex-col px-[1rem] py-3">
                        <CardHeader className={'w-[100%] p-0'}>
                            <p className="text-gray-500">Estado Actual</p>
                            <p className="text-xl font-bold">{trackingFront.estatus}</p>
                        </CardHeader>

                        <CardContent className={'flex flex-row justify-between p-0'}>
                            <div className="flex max-w-[90%] flex-row items-center gap-3 p-0">
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 0 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus - 1 == 1 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="SPR"
                                    onClick={() => AccionSinPrealertar(setTracking, trackingFront, 1)}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 1 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 1 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 2 || trackingFront.ordenEstatus - 1 == 2 || trackingFront.ordenEstatus == 2 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="PDO"
                                    onClick={() => {
                                        AccionPreartar(setTracking, trackingFront, 2);
                                    }}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 2 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 2 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 3 || trackingFront.ordenEstatus - 1 == 3 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="RMI"
                                    onClick={() => {
                                        if (trackingFront.ordenEstatus + 1 == 3 || trackingFront.ordenEstatus - 1 == 3)
                                            AccionRMI(setTracking, trackingFront, setActualizando);
                                    }}
                                />
                                <MoveRight
                                    className={
                                        trackingFront.ordenEstatus > 3 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400 hover:bg-orange-500' : 'size-12 text-gray-400'
                                    }
                                />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 3 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 4 || trackingFront.ordenEstatus - 1 == 4 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="TCR"
                                    onClick={() => {
                                        if (trackingFront.ordenEstatus + 1 == 4 || trackingFront.ordenEstatus - 1 == 4)
                                            AccionTCR(setTracking, trackingFront, setActualizando);
                                    }}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 4 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 4 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 5 || trackingFront.ordenEstatus - 1 == 5 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="PA"
                                    onClick={() => {
                                        if (trackingFront.ordenEstatus + 1 == 5 || trackingFront.ordenEstatus - 1 == 5)
                                            AccionPA(setTracking, trackingFront, setActualizando);
                                    }}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 5 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 5 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 6 || trackingFront.ordenEstatus - 1 == 6 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="OMB"
                                    onClick={() => {
                                        if (trackingFront.ordenEstatus + 1 == 6 || trackingFront.ordenEstatus - 1 == 6)
                                            AccionOMB(setTracking, trackingFront, setActualizando);
                                    }}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 6 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 6 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} ${trackingFront.ordenEstatus + 1 == 7 || trackingFront.ordenEstatus - 1 == 7 ? 'cursor-pointer' : ''} `}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="EN"
                                    onClick={() => {
                                        if (trackingFront.ordenEstatus + 1 == 7 || trackingFront.ordenEstatus - 1 == 7)
                                            AccionEN(setTracking, trackingFront, setActualizando);
                                    }}
                                />
                                <MoveRight className={trackingFront.ordenEstatus > 7 && trackingFront.ordenEstatus < 20? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={`px-7 lg:size-10 xl:size-12 ${trackingFront.ordenEstatus > 7 && trackingFront.ordenEstatus < 20? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'}`}
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="FDO"
                                />
                            </div>

                            <div className="max-w-[10%]">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>

                                            <Cubes
                                                className={`px-7 lg:size-10 xl:size-12 cursor-pointer ${trackingFront.ordenEstatus >= 20 ? 'bg-orange-400 hover:bg-orange-500' : 'bg-gray-400 hover:bg-gray-500'} `}
                                                hijoClassName="font-bold text-lg"
                                                name="OTR"
                                            />

                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Otros Estados</DropdownMenuLabel>

                                        <DropdownMenuItem
                                            key={'btnItemEliminado'}
                                            onClick={async () => {
                                                await AccionEliminar(setTracking, trackingFront, setActualizando);
                                            }}
                                        >
                                            Eliminado
                                        </DropdownMenuItem>

                                        <DropdownMenuItem
                                            key={'btnItemPaquetePerdido'}
                                            onClick={async () => {
                                                await AccionPaquetePerdido(setTracking, trackingFront, setActualizando);
                                            }}
                                        >
                                            Paquete Perdido
                                        </DropdownMenuItem>

                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 justify-between py-3 lg:flex lg:flex-row">
                    <Card className="flex h-auto w-[100%] max-w-[100%] flex-col p-0 lg:max-h-[70vh] lg:w-[49.5%] lg:max-w-[49.5%]">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Encabezado Tracking</p>
                            <Button className="bg-orange-400 px-5 text-sm text-white hover:bg-orange-500">
                                Tracking Siguiente: <CircleX className={'size-5'} />{' '}
                            </Button>
                        </CardHeader>

                        <CardContent className={'flex flex-col overflow-y-auto justify-between px-3 pt-0 pb-2'}>
                            <div className="flex w-[100%] gap-2 pb-2">
                                <InputFloatingLabel
                                    id="descripcion"
                                    type="text"
                                    label="Descripción"
                                    value={trackingFront.descripcion}
                                    classNameContainer={'w-[80%]'}
                                    required
                                    onChange={(e) => {
                                        setTracking((prev) => ({ ...prev, descripcion: e.target.value }));
                                    }}
                                    error={trackingFront.errores.find((error) => error.name == 'descripcion')}
                                />

                                <InputFloatingLabel
                                    id="valorPrealerta"
                                    type="number"
                                    label="Valor"
                                    value={trackingFront.valorPrealerta}
                                    classNameContainer={'w-[20%]'}
                                    required
                                    onChange={(e) => {
                                        setTracking((prev) => ({
                                            ...prev,
                                            valorPrealerta: e.target.value === '' ? null : parseFloat(e.target.value),
                                        }));
                                    }}
                                    error={trackingFront.errores.find((error) => error.name == 'valorPrealerta')}
                                />
                            </div>

                            <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                                <div className="w-[100%] max-w-[100%] items-center gap-3 lg:w-[50%] lg:max-w-[50%]">
                                    <Label htmlFor="desde" className="px-2 text-gray-500">
                                        Desde
                                    </Label>
                                    <Input
                                        type="text"
                                        id="desde"
                                        placeholder="Hasta"
                                        value={tracking.desde !== null ? tracking.desde : 'N/A'}
                                        readOnly
                                    />
                                </div>
                                <div className="w-[100%] max-w-[100%] items-center gap-3 lg:w-[50%] lg:max-w-[50%]">
                                    <Label htmlFor="hasta" className="px-2">
                                        Hasta
                                    </Label>
                                    <Input
                                        type="text"
                                        id="hasta"
                                        placeholder="Hasta"
                                        value={tracking.hasta !== null ? tracking.hasta : 'N/A'}
                                        readOnly
                                    />
                                </div>
                            </div>

                            <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                                <div className="w-[100%] max-w-[100%] items-center gap-3 lg:w-[50%] lg:max-w-[50%]">
                                    <Label htmlFor="destino" className="px-2 text-gray-500">
                                        Destino
                                    </Label>
                                    <Input
                                        type="text"
                                        id="destino"
                                        placeholder="Hasta"
                                        value={tracking.destino !== null ? tracking.destino : 'N/A'}
                                        readOnly
                                    />
                                </div>
                                <div className="w-[100%] max-w-[100%] items-center gap-3 lg:w-[50%] lg:max-w-[50%]">
                                    <Label htmlFor="diasTransito" className="px-2">
                                        Días tránsito
                                    </Label>
                                    <Input
                                        type="text"
                                        id="diasTransito"
                                        placeholder="Días tránsito"
                                        value={tracking.diasTransito !== null ? tracking.diasTransito : 'N/A'}
                                        readOnly
                                    />
                                </div>
                            </div>

                            <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                                <div className="'w-[100%] max-w-[100%] items-center gap-3 lg:w-[50%] lg:max-w-[50%]">
                                    <Label htmlFor="couriers" className="px-2 text-gray-500">
                                        Courier/s
                                    </Label>
                                    <Input type="text" id="couriers" placeholder="Courier/s" value={tracking.couriers} readOnly />
                                </div>

                                <InputFloatingLabel
                                    id="peso"
                                    type="number"
                                    value={tracking.peso}
                                    label="Peso"
                                    classNameContainer={'w-[100%] max-w-[100%] lg:w-[50%] lg:max-w-[50%]'}
                                    readOnly
                                    error={trackingFront.errores.find((error) => error.name == 'peso')}
                                />
                            </div>

                            <div className={'grid grid-cols-1 gap-3 pt-3 lg:flex'}>
                                <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                    <Label htmlFor="proveedor" className="px-2 text-gray-500" required>
                                        Proveedor
                                    </Label>
                                    <Combobox
                                        items={proveedores}
                                        placeholder="Selec. proveedor..."
                                        classNames=" !min-w-[100%] lg:w-60 p-6"
                                        isActive={true}
                                        idSelect={trackingFront.idProveedor}
                                        onChange={(idSeleccionado) =>
                                            CambiarProveedor(idSeleccionado, setTracking, trackingFront, tracking, setMensajeDialog, setMostrarDialog)
                                        }
                                        error={trackingFront.errores.find((error) => error.name == 'idProveedor')}
                                    />
                                </div>

                                <div className={'flex w-[100%] max-w-[100%] flex-col gap-2 lg:w-[50%] lg:max-w-[50%]'}>
                                    <Label htmlFor="cliente" className="px-2 text-gray-500" required>
                                        Cliente
                                    </Label>
                                    <Combobox
                                        items={clientes}
                                        placeholder="Selec. cliente..."
                                        classNames=" !min-w-[100%] lg:w-60 p-6"
                                        isActive={trackingFront.ordenEstatus == 1}
                                        idSelect={trackingFront.idCliente}
                                        onChange={(idSeleccionado) =>
                                            ActualizarClienteYDireccion(idSeleccionado, setDireccionesFront, setCargandoDirecciones, setTracking)
                                        }
                                        error={trackingFront.errores.find((error) => error.name == 'idCliente')}
                                    />
                                </div>
                            </div>

                            <div className="flex w-[100%] flex-col gap-2">
                                <Label htmlFor="direcion" className="px-2 text-gray-500" required>
                                    Dirección
                                </Label>
                                <Combobox
                                    items={direccionesFront}
                                    placeholder={cargandoDirecciones ? 'Cargando Direcciones...' : 'Selec. dirección...'}
                                    classNames="w-60 p-6"
                                    isActive={!cargandoDirecciones}
                                    idSelect={trackingFront.idDireccion}
                                    onChange={(idSeleccionado) =>
                                        setTracking((prev) => ({
                                            ...prev,
                                            idDireccion: idSeleccionado,
                                        }))
                                    }
                                    error={trackingFront.errores.find((error) => error.name == 'idDireccion')}
                                />
                            </div>

                            <div className="relative w-[100%] pb-2">
                                <InputFloatingLabel
                                    id="observaciones"
                                    type="text"
                                    value={trackingFront.observaciones}
                                    label="Observaciones"
                                    classNameContainer={'w-full'}
                                    onChange={(e) =>
                                        setTracking((prev) => ({
                                            ...prev,
                                            observaciones: e.target.value,
                                        }))
                                    }
                                    error={trackingFront.errores.find((error) => error.name == 'observaciones')}
                                />
                            </div>

                            <div className={'flex w-[100%] pb-2'}>
                                <Card className="flex h-auto w-[100%] max-w-[100%] flex-col p-0">
                                    <CardHeader className={'flex w-[100%] flex-row items-center justify-start gap-5 px-4 pt-3'}>
                                        <p className="text-lg text-gray-500">Fotos</p>
                                        <Button
                                            className="size-8 bg-orange-400 px-5 text-sm text-white hover:bg-orange-500"
                                            onClick={() => {
                                                fileInputRef.current?.click();
                                            }}
                                        >
                                            <Plus />
                                        </Button>
                                    </CardHeader>

                                    <CardContent className={'flex justify-start gap-4 overflow-x-scroll px-3 pt-0 pb-2'}>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            ref={fileInputRef}
                                            onChange={(event) => AgregarArchivoSeleccionado(trackingFront, setTracking, event)}
                                            hidden
                                        />
                                        {trackingFront.imagenes.map((imagen) => (
                                            <div className={'relative h-40 w-40 flex-shrink-0'}>

                                                    <img
                                                        src={typeof imagen.archivo === 'string' ? imagen.archivo : URL.createObjectURL(imagen.archivo)}
                                                        alt="Vista previa"
                                                        className="h-full w-full rounded-lg border border-gray-300 object-cover"
                                                    />


                                                {imagen.tipoImagen == 1 && (
                                                    <Button
                                                        className="bg-opacity-50 absolute top-1 right-1 size-8 rounded bg-orange-400 px-5 py-1 text-sm text-white hover:bg-orange-500"
                                                        onClick={() => {
                                                            setTracking((prev) => ({
                                                                ...prev,
                                                                imagenes: prev.imagenes.filter((img) => img.id !== imagen.id),
                                                            }));
                                                        }}
                                                    >
                                                        <Trash />
                                                    </Button>
                                                )}
                                            </div>
                                        ))}
                                    </CardContent>
                                </Card>
                            </div>

                            {trackingFront.ordenEstatus >= 7 && (
                                <div className={'flex w-[100%] gap-2 pb-2'}>
                                    <InputFloatingLabel
                                        id="factura"
                                        type="file"
                                        label="Factura"
                                        classNameContainer={'w-[90%]'}
                                        error={trackingFront.errores.find((error) => error.name == 'factura')}
                                        required
                                        onChange={(event) => IngresoFactura(setTracking, trackingFront, setGuardandoFactura, event)}
                                    />
                                    {/* Si ya hay factura subida, mostrar botón para abrir */}

                                    <Button
                                        type="button"
                                        onClick={() => window.open(trackingFront.factura as string, '_blank')}
                                        className="h-full !w-[10%] text-orange-400"
                                        disabled={!trackingFront?.factura || guardandoFactura}
                                    >
                                        <FileDown className={'size-6 text-white'} />
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="mt-4 flex w-[100%] max-w-[100%] flex-col gap-0 p-0 lg:mt-0 lg:max-h-[70vh] lg:w-[49.5%] lg:max-w-[49.5%]">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Historial Tracking</p>

                            <div className={'flex gap-3'}>
                                <Button
                                    className="cursor-pointer bg-orange-400 px-5 text-sm text-white hover:bg-orange-500"
                                    onClick={() => AgregarHistorialTracking(setHistorialesBocetos, historialesBocetos, tracking.id)}
                                >
                                    <Plus className={'size-5'} />
                                </Button>
                                <Button className="bg-orange-400 px-5 text-lg text-white hover:bg-orange-500">
                                    {trackingFront.historialesTracking.length}
                                </Button>
                            </div>
                        </CardHeader>

                        <CardContent
                            id="card-historialesTracking"
                            className={'flex flex-col justify-between overflow-y-auto !px-0 pt-0 pb-2 lg:max-h-[70vh]'}
                        >
                            {trackingFront.historialesTracking.map((historial) => (
                                <RowHistorialTracking historial={historial} setTracking={setTracking} />
                            ))}
                            {historialesBocetos.map((historialB) => (
                                <RowHistorialTrackingBoceto
                                    historialB={historialB}
                                    setHistorialesBocetos={setHistorialesBocetos}
                                    setTracking={setTracking}
                                    historialesTracking={trackingFront.historialesTracking}
                                />
                            ))}
                        </CardContent>
                    </Card>
                </div>

                {/* Notificacion Dialog */}

                {mostrarDialog && (
                    <Dialog open={mostrarDialog} onOpenChange={(open) => setMostrarDialog(open)}>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{mensajeDialog.titulo}</DialogTitle>
                                <DialogDescription>{mensajeDialog.descripcion}</DialogDescription>
                            </DialogHeader>
                            <DialogFooter className="sm:justify-start">
                                <DialogClose asChild>
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        className={'bg-black text-white hover:bg-gray-500'}
                                        onClick={() => setMostrarDialog(false)}
                                    >
                                        Entendido
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                )}

                {/* TOASTER PARA MENSAJES */}
                <Toaster position="top-center" />
                <Spinner isActive={actualizando}></Spinner>
            </MainContainer>
        </AppLayout>
    );
}

function RowHistorialTracking({
    historial,
    setTracking,
}: {
    historial: HistorialTracking;
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>;
}) {
    const [verModificada, setVerModificada] = useState<boolean>(historial.descripcionModificada != '');
    const [editar, setEditar] = useState<boolean>(false);
    const [descripcionActualizando, setDescripcionActualizando] = useState<string>(
        historial.descripcionModificada != '' ? historial.descripcionModificada : historial.descripcion,
    );

    useEffect(() => {
        console.log(verModificada);
    }, [verModificada]);

    function SwitchOcultarHistorial() {
        setTracking((prev) => ({
            ...prev,
            historialesTracking: prev.historialesTracking.map((historialPrev) =>
                historialPrev.id === historial.id
                    ? {
                          ...historialPrev,
                          ocultado: !historialPrev.ocultado,
                          actions: historialPrev.actions.map((actionPrev) =>
                              actionPrev.actionType === 'SwitchOcultar'
                                  ? {
                                        ...actionPrev,
                                        descripcion: actionPrev.descripcion == 'Ocultar' ? 'Mostrar' : 'Ocultar',
                                        icon: actionPrev.icon == 'EyeOff' ? 'Eye' : 'EyeOff',
                                    }
                                  : actionPrev,
                          ),
                      }
                    : historialPrev,
            ),
        }));
    }

    function SwtichMostrarOriginal() {
        setVerModificada((prevEstado) => {
            const nuevoVerModificada = !prevEstado; // este es el valor correcto
            setTracking((prev) => ({
                ...prev,
                historialesTracking: prev.historialesTracking.map((historialPrev) =>
                    historialPrev.id === historial.id
                        ? {
                              ...historialPrev,
                              actions: historialPrev.actions.map((actionPrev) =>
                                  actionPrev.actionType === 'VerOriginal'
                                      ? {
                                            ...actionPrev,
                                            descripcion: nuevoVerModificada ? 'Ver Original' : 'Ver Propia',
                                        }
                                      : actionPrev,
                              ),
                          }
                        : historialPrev,
                ),
            }));
            return nuevoVerModificada; // devolvemos el valor correcto
        });
    }

    //Funciones para editar
    function ActualizarDescripcionHistorial(valorInput: string) {
        setDescripcionActualizando(valorInput);
    }

    function ActualizarHistorial() {
        // 1. Validar si no es un campo vacío
        // 2. Actualizar el campo de descripcionActualizada
        // 3. verModificada = true
        // 4. editar = false

        // 1. Validar si no es un campo vacío
        if (descripcionActualizando == '') {
            ErrorModal('Error al actualizar historial', 'La descripción no debe estar vacía.');
            return;
        }

        // 2. Actualizar el campo de descripcionActualizada
        setTracking((prevTracking) => ({
            ...prevTracking,
            historialesTracking: prevTracking.historialesTracking.map((historialPrev) =>
                historialPrev.id == historial.id
                    ? {
                          ...historialPrev,
                          descripcionModificada: descripcionActualizando,
                          actions: historialPrev.actions.map((actionPrev) =>
                              actionPrev.actionType === 'VerOriginal'
                                  ? {
                                        ...actionPrev,
                                        descripcion: 'Ver Original',
                                        isActive: true,
                                    }
                                  : actionPrev,
                          ),
                      }
                    : historialPrev,
            ),
        }));

        // 3. verModificada = true
        setVerModificada(true);
        // 4. editar = false
        setEditar(false);
    }

    function CancelarActualizar() {
        // 1. SetEditar = false
        // 2. DescripcionActualizando = como estaba antes

        setEditar(false);

        setDescripcionActualizando(historial.descripcionModificada != '' ? historial.descripcionModificada : historial.descripcion);
    }

    return (
        <div className={`flex items-center gap-3 border-b px-5 py-3 ${historial.ocultado ? 'bg-orange-50' : ''}`}>
            <Button
                className={
                    historial.tipo == 1
                        ? 'text-md bg-green-300 px-[5px] text-white'
                        : historial.tipo == 2
                          ? 'text-md bg-red-400 px-[5px] text-white'
                          : historial.tipo == 3
                            ? 'text-md bg-blue-400 px-[5px] text-white'
                            : 'text-md bg-black px-[5px] text-white'
                }
            >
                {historial.tipo == 1 ? 'PA' : historial.tipo == 2 ? 'MB' : historial.tipo == 3 ? 'AP' : 'OT'}
            </Button>

            {!editar ? (
                <div className={'flex max-w-[68%] min-w-[68%] flex-col'}>
                    <span className={'pb-2 text-justify text-[13px]'}>
                        {!verModificada ? historial.descripcion : historial.descripcionModificada}
                    </span>
                    <span className={'text-[12px] text-gray-500'}>{historial.paisEstado}</span>
                </div>
            ) : (
                <div className={'flex max-w-[67.53%] min-w-[67.53%] flex-col'}>
                    <InputFloatingLabel
                        id={'historialT-' + historial.id}
                        type="text"
                        value={descripcionActualizando}
                        label="Descripción"
                        classNameContainer={''}
                        onChange={(e) => ActualizarDescripcionHistorial(e.target.value)}
                        required
                    />
                </div>
            )}

            <div className={'flex min-w-[17%] flex-col'}>
                <div className={'flex items-center gap-1 pb-3 text-[13px]'}>
                    <Calendar className={'size-5 text-orange-400'} /> {historial.fecha}
                </div>
                <div className={'flex items-center gap-1 text-[13px]'}>
                    <Clock4 className={'size-5 text-orange-400'} /> {historial.hora}
                </div>
            </div>

            {!editar ? (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="size-10">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                        {historial.actions.map((action) => {
                            const LucideIcon: LucideIcon = iconMap[action.icon ?? 'UserPlus'];
                            return (
                                <DropdownMenuItem
                                    onClick={() => {
                                        if (action.actionType === 'Editar') {
                                            setEditar(true);
                                        } else if (action.actionType === 'VerOriginal') {
                                            SwtichMostrarOriginal();
                                        } else if (action.actionType === 'SwitchOcultar') {
                                            SwitchOcultarHistorial();
                                        } else {
                                            console.log('Error la acción no coincide');
                                        }
                                    }}
                                    className={!action.isActive ? 'hidden' : ''}
                                >
                                    <div className="flex flex-nowrap items-center gap-2">
                                        <LucideIcon /> <span>{action.descripcion} </span>
                                    </div>
                                </DropdownMenuItem>
                            );
                        })}
                    </DropdownMenuContent>
                </DropdownMenu>
            ) : (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="size-10">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Acciones</DropdownMenuLabel>

                        <DropdownMenuItem onClick={() => ActualizarHistorial()}>
                            <div className="flex flex-nowrap items-center gap-2">
                                <CircleCheck /> <span>Actualizar</span>
                            </div>
                        </DropdownMenuItem>

                        <DropdownMenuItem onClick={() => CancelarActualizar()}>
                            <div className="flex flex-nowrap items-center gap-2">
                                <CircleX /> <span>Cancelar</span>
                            </div>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </div>
    );
}

function RowHistorialTrackingBoceto({
    historialB,
    setHistorialesBocetos,
    setTracking,
    historialesTracking,
}: {
    historialB: HistorialTracking;
    setHistorialesBocetos: React.Dispatch<React.SetStateAction<HistorialTracking[]>>;
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>;
    historialesTracking: HistorialTracking[];
}) {
    const inputRef = useRef<HTMLInputElement>(null);

    // Cuando se monta el componente, hace focus
    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    function ActualizarDescripcionHistorialBoceto(nuevaDescripcion: string) {
        setHistorialesBocetos((prevHistoriales) =>
            prevHistoriales.map((historial) =>
                historial.id == historialB.id
                    ? {
                          ...historial,
                          descripcion: nuevaDescripcion,
                      }
                    : historial,
            ),
        );
    }

    function GuardarHistorialBoceto() {
        // 1. Validar si no es un campo vacío
        // 2. Editar el id del H.Boceto para que sea unico
        // 2.1. Agregar el historial al arreglo de tracking
        // 3. Eliminar el historial del arreglo de bocetos

        // 1. Validar si no es un campo vacío
        if (historialB.descripcion.trim() == '') {
            ErrorModal('Error al guardar historial', 'El historial a guardar está vacío');
            return;
        }

        // 2. Editar el id del H.Boceto para que sea unico
        let idUnico: number = -1;

        for (const historial of historialesTracking) {
            console.log(historial);
            if (historial.id == idUnico) idUnico--;
        }

        historialB.id = idUnico;

        // 2.1. Agregar el historial al arreglo de tracking

        setTracking((prevTracking) => ({
            ...prevTracking,
            historialesTracking: [...prevTracking.historialesTracking, historialB],
        }));

        // 3. Eliminar el historial del arreglo de bocetos
        EliminarHistorialBoceto();
    }

    function EliminarHistorialBoceto() {
        //Elimina del arreglo de historialesBocetos
        setHistorialesBocetos((prevHistoriales) => prevHistoriales.filter((historialF) => historialF.id !== historialB.id));
    }

    return (
        <div className={`flex items-center gap-3 border-b px-5 py-3`}>
            <Button className="text-md bg-red-400 px-[5px] text-white">MB</Button>

            <div className={'flex max-w-[67.53%] min-w-[67.53%] flex-col'}>
                <InputFloatingLabel
                    id={'historialT-' + historialB.id}
                    type="text"
                    value={historialB.descripcion}
                    label="Descripción"
                    classNameContainer={''}
                    onChange={(e) => ActualizarDescripcionHistorialBoceto(e.target.value)}
                    required
                    ref={inputRef}
                />
            </div>

            <div className={'flex min-w-[17%] flex-col'}>
                <div className={'flex items-center gap-1 pb-3 text-[13px]'}>
                    <Calendar className={'size-5 text-orange-400'} /> {historialB.fecha}
                </div>
                <div className={'flex items-center gap-1 text-[13px]'}>
                    <Clock4 className={'size-5 text-orange-400'} /> {historialB.hora}
                </div>
            </div>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="size-10">
                        <span className="sr-only">Open menu</span>
                        <MoreHorizontal />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Acciones</DropdownMenuLabel>

                    <DropdownMenuItem onClick={() => GuardarHistorialBoceto()}>
                        <div className="flex flex-nowrap items-center gap-2">
                            <Plus /> <span>Agregar</span>
                        </div>
                    </DropdownMenuItem>

                    <DropdownMenuItem onClick={() => EliminarHistorialBoceto()}>
                        <div className="flex flex-nowrap items-center gap-2">
                            <Trash /> <span>Eliminar</span>
                        </div>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

function AgregarHistorialTracking(
    setHistorialesBocetos: React.Dispatch<React.SetStateAction<HistorialTracking[]>>,
    historialesBocetos: HistorialTracking[],
    id: number,
) {
    // Agregar un historialTrackingBoceto al array

    let idUnico: number = -1;

    for (const historial of historialesBocetos) {
        console.log('FOR: ' + historial.id);
        if (historial.id === idUnico) {
            idUnico--;
        }
    }

    const hoyTimestamp = Date.now();

    const opcionesFecha = { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'America/Costa_Rica' };
    const opcionesHora = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'America/Costa_Rica' };

    // @ts-expect-error
    const fechaCostaRica = new Intl.DateTimeFormat('es-CR', opcionesFecha).format(hoyTimestamp);
    // @ts-expect-error
    const horaCostaRica = new Intl.DateTimeFormat('es-CR', opcionesHora).format(hoyTimestamp);

    const historialBoceto: HistorialTracking = {
        id: idUnico,
        descripcion: '',
        descripcionModificada: '',
        codigoPostal: 40801,
        paisEstado: 'San Joaquín de Flores, Heredia',
        ocultado: false,
        tipo: 2,
        fecha: fechaCostaRica,
        hora: horaCostaRica,
        idTracking: id,
        perteneceEstado: null,
        fechaCompleta: hoyTimestamp,
        actions: [
            {
                descripcion: 'Editar',
                icon: 'Edit',
                actionType: 'Editar',
                isActive: true,
                route: '',
            },
            {
                descripcion: 'Ver Original',
                icon: 'ArrowLeftRight',
                actionType: 'VerOriginal',
                isActive: false,
                route: '',
            },
            {
                descripcion: 'Ocultar',
                icon: 'EyeOff',
                actionType: 'SwitchOcultar',
                isActive: true,
                route: '',
            },
        ],
    };

    setHistorialesBocetos((prevHistoriales) => [...prevHistoriales, historialBoceto]);
}

// Funciones del componente principal

async function ActualizarClienteYDireccion(
    idCliente: number,
    setDirecciones: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>,
    setCargandoDirecciones: React.Dispatch<React.SetStateAction<boolean>>,
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>,
) {
    // 1. Poner cargando para que el select de direcciones se bloquee
    // 2. Llamar al API para que me retorne las direcciones pero en forma de ComboBoxItem[]
    // 3. Actualizar el cliente de tracking para tener el # de tel
    // 4. borrar la direccion enlazada del tracking y agregar el idCliente

    try {
        // 1. Poner cargando para que el select de direcciones se bloquee
        setCargandoDirecciones(true);

        // 2. Llamar al API para que me retorne las direcciones pero en forma de ComboBoxItem[]
        const direccionesComboboxItems: ComboBoxItem[] = await comboboxDirecciones(idCliente);
        setDirecciones(direccionesComboboxItems);

        // 3. Actualizar el cliente de tracking para tener el # de tel
        const clienteCompleto: ClienteCompleto = await ObtenerCliente(idCliente);
        const clienteTracking: ClienteTracking = {
            id: clienteCompleto.id,
            nombre: clienteCompleto.nombre,
            apellidos: clienteCompleto.apellidos,
            telefono: clienteCompleto.telefono,
        };

        setTracking((prev) => ({
            ...prev,
            cliente: clienteTracking,
        }));

        setCargandoDirecciones(false);

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        // si hay un error, hay que mostrar direcciones vacias y el tracking actualizarlo con idDireccion nulo
        ErrorModal('Error al cargar las direcciones', 'Hubo un error al cargar las direcciones');
        setDirecciones([]);

        // 4. borrar la direccion enlazada del tracking
    } finally {
        setTracking((prev) => ({
            ...prev,
            idDireccion: -1,
            idCliente: idCliente,
        }));
    }
}

async function CambiarProveedor(
    idProveedor: number,
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>,
    tracking: TrackingCompleto,
    trackingBack: TrackingCompleto,
    setMensajeDialog: React.Dispatch<React.SetStateAction<MensajeDialog>>,
    setMostrarDialog: React.Dispatch<React.SetStateAction<boolean>>,
) {
    // 1. Alertar de todos los posibles casos:
    // 1.1. Si pasa de ML a Aeropost, no poner nada
    // 1.2. Si pasa de AP a ML, indicar que se va a borrar la prealerta de la app de AP y actualizar la de la app

    // PSTD: Si hay un cambio del proveedor y esta en otro estado != SPR o PDO, hay que cambiarlo a PDO

    // 1.3. Si no ponen ningun proveedor:
    // 1.3.1: Si esta en SPR, no pasa nada
    // 1.3.2: Si esta en otro, va a poner un error porque ya hay una prealerta registrada con el proveedor {nombreProveedor}. este error se pone como error, no con modal, porque se vuelve a verificar cuando quiera guardar o prealertar
    // 1.2. Si pasa de AP a ML, indicar que se va a borrar la prealerta de la app de AP y actualizar la de la app
    // tomar en cuenta de no mostrar el mensaje del proveedor si cambia del nuevo proveedor al nuevo, xq la alerta no tendria sentido
    if (trackingBack.idProveedor !== idProveedor && idProveedor !== -1 && tracking.ordenEstatus > 1) {
        setMensajeDialog({
            titulo: 'Alerta sobre cambiar de proveedor',
            descripcion:
                'Si cambia el proveedor con el que prealertó y después guarda los cambios, se va a borrar la prealerta del proveedor anterior.',
        });

        setMostrarDialog(true);
    }

    setTracking((prev) => ({
        ...prev,
        idProveedor: idProveedor,
    }));

    // PSTD: Si hay un cambio del proveedor y esta en otro estado != SPR(1) o PDO(2), hay que cambiarlo a PDO
    if (tracking.ordenEstatus > 2) {
        setTracking((prev) => ({
            ...prev,
            ordenEstatus: 2,
            estatus: 'Prealertado',
            ordenEstatusSincronizado: 2,
        }));
    }

    // 1.3. Si no ponen ningun proveedor:

    // 1.3.1: Si esta en SPR, no pasa nada

    // 1.3.2: Si esta en otro de SPR, va a poner un error porque ya hay una prealerta registrada con el proveedor {nombreProveedor}. este error se pone como error, no con modal, porque se vuelve a verificar cuando quiera guardar o prealertar
    if (tracking.ordenEstatus > 1 && idProveedor == -1) {
        setTracking((prev) => ({
            ...prev,
            idProveedor: idProveedor,
            errores: [
                {
                    name: 'idProveedor',
                    message: 'Ya hay una prealerta registrada con el proveedor: ' + prev.nombreProveedor + '. Indicar proveedor o dejar el anterior.',
                },
            ],
        }));
    } else {
        setTracking((prev) => ({
            ...prev,
            errores: [],
        }));
    }
}

function AgregarArchivoSeleccionado(
    tracking: TrackingCompleto,
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>,
    event: React.ChangeEvent<HTMLInputElement>,
) {
    // # Funcion para agregar los archivos seleccionados a la galeria de archivos del tracking

    const file = event.target.files?.[0];

    if (!file) return;

    // Obtener el idUnico de imagen
    let idUnico: number = -1;

    for (const img of tracking.imagenes) {
        if (img.id == idUnico) idUnico--;
    }

    const imagen: Imagen = {
        id: idUnico,
        archivo: file,
        tipoImagen: 1,
    };

    if (file) {
        setTracking((prev) => ({
            ...prev,
            imagenes: [...prev.imagenes, imagen],
        }));
    }
}

function ContactarCliente(trackingFront: TrackingCompleto) {
    // 1. Se le envia a otra persaña para conversar con el cliente
    // 2. En el mensaje se pone un mensaje predefinido

    try {
        // Limpiamos el número (por si viene con espacios o +)
        const telefono: string = String(trackingFront.cliente.telefono);
        const numero = telefono.replace(/\D/g, '');
        const url = `https://wa.me/506${numero}`; // o sin 506 si ya viene con código país

        window.open(url, '_blank'); // abre en una nueva pestaña
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al contactar cliente', 'Hubo un error al contactar el cliente. Vuelve a intentarlo o contacta al soporte TI');
    }
}

async function ActualizarTrackingAux(trackingFront: TrackingCompleto, setSincronizando: React.Dispatch<React.SetStateAction<boolean>>) {
    // 1. Verificar el estado actual del tracking dicho por el trabajador
    // 2. Dependiendo del estado actual, se hacen las validaciones necesarias para saber si se puede guardar o no

    setSincronizando(true);
    try {
        await ActualizarTracking(trackingFront);
        ExitoModal('Actualización Exitosa', 'Se actualizó el tracking correctamente.')
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        ErrorModal('Error al actualizar tracking', 'Hubo un error a lactualizar el tracking. Intentelo de nuevo o contacte el soporte de TI');
    }finally {
        setSincronizando(false);
    }
}

async function IngresoFactura(
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>,
    tracking: TrackingCompleto,
    setGuardandoFactura: React.Dispatch<React.SetStateAction<boolean>>,
    event,
) {
    // 1. Se va a ingresar a setTracking
    // 2. Se va a actualizar el estado como FDO
    setGuardandoFactura(true);

    try {
        const factura = event.target.files?.[0];
        const trackingParam: TrackingCompleto = {
            ...tracking,
            factura: factura,
            ordenEstatus: 8,
        };
        const trackingRespuesta: TrackingCompleto = await SubirFactura(trackingParam);
        setTracking(trackingRespuesta);

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        console.log(e);
        ErrorModal('Error al subir la factura', 'Hubo un error la factura del tracking. Vuelvalo a intentarlo o contacte con soporte TI.');
    } finally {
        setGuardandoFactura(false);
    }
}

async function SincronizarCambiosAux(trackingFront: TrackingCompleto, setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>, setActualizando: React.Dispatch<React.SetStateAction<boolean>>) {
    // - Se van a guardar los cambios actuales y sincronizar los cambios con ambas APIs de ParcelsApp y Aeropost
    // 1. Mostrar el spinner porque se está actualizando la data
    // 2. GuardarSincronizar datos con las demas APIs
    // 3. Igualarlo a trackingFront
    // 4. Quitar el spinner

    // 1. Mostrar el spinner porque se está actualizando la data
    setActualizando(true);

    try{
        // 2. GuardarSincronizar datos con las demas APIs
        setTracking(await SincronizarCambios(trackingFront));

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    }catch(e){
        console.log(e)
        ErrorModal('Error al sincronizar cambios', 'Hubo un error al sincronizar cambios. Vuelvalo a intentar o contacte con soporte TI.');
    }finally {
        // 4. Quitar el spinner
        setActualizando(false);
    }


}
