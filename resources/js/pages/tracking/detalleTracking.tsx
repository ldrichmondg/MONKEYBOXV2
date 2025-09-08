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
import { Box, Calendar, CircleCheck, CircleX, Clock4, LucideIcon, MoreHorizontal, MoveRight, Plus, RotateCcw, Trash } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

//api calls
import { iconMap } from '@/lib/iconMap';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { cargarProveedores } from '@/servicesFront/proveedor/servicioFrontProveedores';
import { HistorialTracking } from '@/types/historialTracking';
import { comboboxDirecciones } from '@/servicesFront/direccion/servicioFrontDireccion';

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

    //variables de consulta:
    const [cargandoDirecciones, setCargandoDirecciones] = useState<boolean>(false);

    useEffect(() => {
        cargarProveedores(setProveedores);
    }, []);

    useEffect(() => {
        console.log(trackingFront);
    }, [trackingFront]);

    useEffect(() => {
        console.log(historialesBocetos);
    }, [historialesBocetos]);

    useEffect(() => {
        console.log(direccionesFront);
    }, [direccionesFront]);

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
                                <p className="text-bold"> {trackingFront.trackingProveedor} </p>
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
                                        onChange={(idSeleccionado) =>
                                            ActualizarClienteYDireccion(idSeleccionado, setDireccionesFront, setCargandoDirecciones, setTracking)
                                        }
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
    function ActualizarDescripcionHistorial(valorInput) {
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

    function ActualizarDescripcionHistorialBoceto(nuevaDescripcion: number) {
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

        console.log(idUnico);
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

    const fechaCostaRica = new Intl.DateTimeFormat('es-CR', opcionesFecha).format(hoyTimestamp);
    const horaCostaRica = new Intl.DateTimeFormat('es-CR', opcionesHora).format(hoyTimestamp);

    const historialBoceto: HistorialTracking = {
        id: idUnico,
        descripcion: '',
        descripcionModificada: '',
        codigoPostal: '40801',
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
    // 3. borrar la direccion enlazada del tracking y agregar el idCliente
    try {
        // 1. Poner cargando para que el select de direcciones se bloquee
        setCargandoDirecciones(true);

        // 2. Llamar al API para que me retorne las direcciones pero en forma de ComboBoxItem[]
        const direccionesComboboxItems: ComboBoxItem[] = await comboboxDirecciones(idCliente);
        setDirecciones(direccionesComboboxItems);

        setCargandoDirecciones(false);

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
        console.error(e);
        // si hay un error, hay que mostrar direcciones vacias y el tracking actualizarlo con idDireccion nulo
        ErrorModal('Error al cargar las direcciones', 'Hubo un error al cargar las direcciones');
        setDirecciones([]);

        // 3. borrar la direccion enlazada del tracking
    } finally {
        setTracking( (prev) => ({
            ...prev,
            idDireccion: null,
            idCliente: idCliente
        }))
    }
}
