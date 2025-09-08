import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import Cubes from '@/ownComponents/cubes';
import InputFloatingLabel from '@/ownComponents/inputFloatingLabels';
import { WhatsappIcon } from '@/ownComponents/svgs/whatsApp';

import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Combobox } from '@/ownComponents/combobox';
import { type BreadcrumbItem, ButtonHeader, ComboBoxItem } from '@/types';
import { TrackingCompleto } from '@/types/tracking';
import { Head } from '@inertiajs/react';
import { Box, Calendar, CircleX, Clock4, LucideIcon, MoreHorizontal, MoveRight, Plus, RotateCcw } from 'lucide-react';
import React, { useEffect, useState } from 'react';

//api calls
import { iconMap } from '@/lib/iconMap';
import { cargarProveedores } from '@/servicesFront/proveedor/servicioFrontProveedores';
import { HistorialTracking } from '@/types/historialTracking';

interface Props {
    tracking: TrackingCompleto;
    clientes: ComboBoxItem[];
    direcciones: ComboBoxItem[];
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

const buttons: ButtonHeader[] = [
    {
        id: 'sincronizarCambios',
        name: 'Sincronizar Cambios',
        className: 'bg-orange-400 text-white hover:bg-orange-500 ',
        isActive: true,
        onClick: null,
        icon: RotateCcw,
    },
    {
        id: 'contactarCliente',
        name: 'Contactar Cliente',
        className: 'bg-green-400 text-white hover:bg-green-300 ',
        isActive: true,
        onClick: null,
        icon: WhatsappIcon,
    },
    {
        id: 'guardarCambios',
        name: 'Guardar cambios',
        className: 'bg-red-400 text-white hover:bg-red-300 ',
        isActive: true,
        onClick: null,
    },
];

export default function DetalleTracking({ tracking, clientes, direcciones }: Props) {
    const [ordenEstado, setOrdenEstado] = useState<number>();
    const [proveedores, setProveedores] = useState<ComboBoxItem[]>([]);
    const [direccionesFront, setDireccionesFront] = useState<ComboBoxItem[]>(direcciones);
    const [trackingFront, setTracking] = useState<TrackingCompleto>(tracking);
    const [historialesBocetos, setHistorialesBocetos] = useState<HistorialTracking[]>([]);
    useEffect(() => {
        cargarProveedores(setProveedores);
    }, []);

    //console.log(trackingFront);
    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title={trackingFront.idTracking + '- MBox'} />
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
                            {trackingFront.nombreProveedor == 'MiLocker' ? (
                                <Input
                                    id="trackingProveedor"
                                    className="text-bold"
                                    placeholder="Indique el #proveedor..."
                                    value={trackingFront.trackingProveedor}
                                />
                            ) : (
                                <p className="text-xl font-semibold"> {trackingFront.trackingProveedor} </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="flex w-[75%] max-w-[75%] flex-col px-[1rem] py-3">
                        <CardHeader className={'w-[100%] p-0'}>
                            <p className="text-gray-500">Estado Actual</p>
                            <p className="text-xl font-bold">{tracking.estatus}</p>
                        </CardHeader>

                        <CardContent className={'flex flex-row justify-between p-0'}>
                            <div className="flex max-w-[90%] flex-row items-center gap-3 p-0">
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 0
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="SPR"
                                />
                                <MoveRight className={tracking.ordenEstatus > 1 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 1
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="PDO"
                                />
                                <MoveRight className={tracking.ordenEstatus > 2 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 2
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="RMI"
                                />
                                <MoveRight className={tracking.ordenEstatus > 3 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 3
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="TCR"
                                />
                                <MoveRight className={tracking.ordenEstatus > 4 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 4
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="PA"
                                />
                                <MoveRight className={tracking.ordenEstatus > 5 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 5
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="OMB"
                                />
                                <MoveRight className={tracking.ordenEstatus > 6 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 6
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="EN"
                                />
                                <MoveRight className={tracking.ordenEstatus > 7 ? 'size-12 text-orange-400' : 'size-12 text-gray-400'} />
                                <Cubes
                                    className={
                                        tracking.ordenEstatus > 7
                                            ? 'bg-orange-400 px-7 lg:size-10 xl:size-12'
                                            : 'bg-gray-400 px-7 lg:size-10 xl:size-12'
                                    }
                                    hijoClassName="font-bold xl:text-lg lg:text-md md:text-sm"
                                    name="FDO"
                                />
                            </div>

                            <div className="max-w-[10%]">
                                <Cubes className="size-12 bg-gray-400 px-7" hijoClassName="font-bold text-lg" name="OTR" />
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

                        <CardContent className={'flex flex-col justify-between px-3 pt-0 pb-2'}>
                            <div className="relative w-[100%] pb-2">
                                <InputFloatingLabel
                                    id="descripcion"
                                    type="text"
                                    label="Descripción"
                                    value={tracking.descripcion}
                                    classNameContainer={'w-full'}
                                    required
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
                                    required
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
                                        idSelect={tracking.idProveedor}
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
                                        isActive={true}
                                        idSelect={tracking.idCliente}
                                    />
                                </div>
                            </div>

                            <div className="flex w-[100%] flex-col gap-2">
                                <Label htmlFor="direcion" className="px-2 text-gray-500" required>
                                    Dirección
                                </Label>
                                <Combobox
                                    items={direccionesFront}
                                    placeholder="Selec. dirección..."
                                    classNames="w-60 p-6"
                                    isActive={true}
                                    idSelect={tracking.idDireccion}
                                />
                            </div>

                            <div className="relative w-[100%] pb-2">
                                <InputFloatingLabel
                                    id="observaciones"
                                    type="text"
                                    value={tracking.observaciones}
                                    label="Observaciones"
                                    classNameContainer={'w-full'}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="mt-4 flex w-[100%] max-w-[100%] flex-col gap-0 p-0 lg:mt-0 lg:max-h-[70vh] lg:w-[49.5%] lg:max-w-[49.5%]">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Historial Tracking</p>

                            <div className={'flex gap-3'}>
                                <Button
                                    className="cursor-pointer bg-orange-400 px-5 text-sm text-white hover:bg-orange-500"
                                    onClick={() => AgregarHistorialTracking(setHistorialesBocetos, historialesBocetos)}
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
                                <RowHistorialTrackingBoceto historialB={historialB} setTracking={setTracking} />
                            ))}
                        </CardContent>
                    </Card>
                </div>
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

            <div className={'flex max-w-[68%] min-w-[68%] flex-col'}>
                <span className={'pb-2 text-justify text-[13px]'}>{historial.descripcion}</span>
                <span className={'text-[12px] text-gray-500'}>{historial.paisEstado}</span>
            </div>

            <div className={'flex min-w-[17%] flex-col'}>
                <div className={'flex items-center gap-1 pb-3 text-[13px]'}>
                    {' '}
                    <Calendar className={'size-5 text-orange-400'} /> {historial.fecha}
                </div>
                <div className={'flex items-center gap-1 text-[13px]'}>
                    {' '}
                    <Clock4 className={'size-5 text-orange-400'} /> {historial.hora}
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
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    {historial.actions.map((action) => {
                        const LucideIcon: LucideIcon = iconMap[action.icon ?? 'UserPlus'];
                        return (
                            <DropdownMenuItem
                                onClick={() => {
                                    if (action.actionType === 'Editar') {
                                        alert('Editar');
                                    } else if (action.actionType === 'VerOriginal') {
                                        alert('ver original');
                                    } else if (action.actionType === 'SwitchOcultar') {
                                        SwitchOcultarHistorial();
                                    } else {
                                        console.log('Error la acción no coincide');
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
        </div>
    );
}

function RowHistorialTrackingBoceto({
    historialB,
    setTracking,
}: {
    historialB: HistorialTracking;
    setTracking: React.Dispatch<React.SetStateAction<TrackingCompleto>>;
}) {
    return (
        <div className={`flex items-center gap-3 border-b px-5 py-3`}>
            <Button className="text-md bg-red-400 px-[5px] text-white">MB</Button>

            <div className={'flex max-w-[68%] min-w-[68%] flex-col'}>
                <Label htmlFor={'historialT-' + historialB.id}>Descripción</Label>
                <Input type={'text'} name={'historialT-' + historialB.id} className={''}></Input>
            </div>

            <div className={'flex min-w-[17%] flex-col'}>
                <div className={'flex items-center gap-1 pb-3 text-[13px]'}>
                    {' '}
                    <Calendar className={'size-5 text-orange-400'} /> {historialB.fecha}
                </div>
                <div className={'flex items-center gap-1 text-[13px]'}>
                    {' '}
                    <Clock4 className={'size-5 text-orange-400'} /> {historialB.hora}
                </div>
            </div>

            <Button className={'bg-orange-400'}>
                <Plus />
            </Button>
        </div>
    );
}

function AgregarHistorialTracking(
    setHistorialesBocetos: React.Dispatch<React.SetStateAction<HistorialTracking[]>>,
    historialesBocetos: HistorialTracking[],
) {
    // Agregar un historialTrackingBoceto al array

    let idUnico: number = -1;

    historialesBocetos.map((historial) => {
        if (historial.id === idUnico) {
            idUnico--;
        }
    });

    // const historialBoceto: HistorialTracking = {
    //     id: idUnico,
    //     descripcion: '',
    //     descripcionModificada: '',
    //     codigoPostal: '40801',
    //     paisEstado: 'San Joaquín de Flores, Heredia',
    //     ocultado: false,
    //     tipo:
    // }
}
