import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import Cubes from '@/ownComponents/cubes';
import InputFloatingLabel from '@/ownComponents/inputFloatingLabels';
import { WhatsappIcon } from '@/ownComponents/svgs/whatsApp';

import { Card, CardContent, CardHeader } from '@/components/ui/card';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem, ButtonHeader, ComboBoxItem } from '@/types';
import { TrackingCompleto } from '@/types/tracking';
import { Head } from '@inertiajs/react';
import { Box, CircleX, MoveRight, RotateCcw } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { Combobox } from '@/ownComponents/combobox';

//api calls
import { cargarProveedores } from '@/servicesFront/proveedor/servicioFrontProveedores';
import { Skeleton } from '@/components/ui/skeleton';


interface Props {
    tracking: TrackingCompleto;
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

export default function DetalleTracking({ tracking }: Props) {
    const [ordenEstado, setOrdenEstado] = useState<number>();
    const [proveedores, setProveedores] = useState<ComboBoxItem[]>([]);
    const [clientes, setClientes] = useState<ComboBoxItem[]>([]);
    const [direcciones, setDirecciones] = useState<ComboBoxItem[]>([]);

    useEffect(() => {
        cargarProveedores(setProveedores);
    }, []);

    console.log(tracking);
    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title={tracking.idTracking + '- MBox'} />
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
                            <p className="font-bold text-gray-500"> {tracking.idTracking}</p>
                            {tracking.nombreProveedor == 'MiLocker' ? (
                                <Input
                                    id="trackingProveedor"
                                    className="text-bold"
                                    placeholder="Indique el #proveedor..."
                                    value={tracking.trackingProveedor}
                                />
                            ) : (
                                <p className="text-bold"> {tracking.trackingProveedor} </p>
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

                <div className="py-3">
                    <Card className="flex w-[50%] max-w-[50%] flex-col p-0">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Encabezado Tracking</p>
                            <Button className="bg-orange-400 px-5 text-sm text-white hover:bg-orange-500">
                                Tracking Siguiente: <CircleX className={'size-5'} />{' '}
                            </Button>
                        </CardHeader>

                        <CardContent className={'flex flex-col justify-between px-3 pt-0 pb-2'}>
                            <div className="relative w-[100%] pb-2">
                                <InputFloatingLabel id="descripcion" type="text" label="Descripción" value={tracking.descripcion}  classNameContainer={'w-full'} required />
                            </div>

                            <div className={'flex gap-3 pt-3'}>
                                <div className="w-[50%] max-w-sm items-center gap-3">
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
                                <div className="w-[50%] max-w-sm items-center gap-3">
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

                            <div className={'flex gap-3 pt-3'}>
                                <div className="w-[50%] max-w-sm items-center gap-3">
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
                                <div className="w-[50%] max-w-sm items-center gap-3">
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

                            <div className={'flex gap-3 pt-3'}>
                                <div className="w-[50%] max-w-sm items-center gap-3">
                                    <Label htmlFor="couriers" className="px-2 text-gray-500">
                                        Courier/s
                                    </Label>
                                    <Input type="text" id="couriers" placeholder="Courier/s" value={tracking.couriers} readOnly />
                                </div>

                                <InputFloatingLabel id="peso" type="number" value={tracking.peso} label="Peso" classNameContainer={'w-[50%]'} required />
                            </div>

                            <div className={'flex gap-3 pt-3'}>
                                <div className={'w-[50%] flex flex-col gap-2'}>
                                    <Label htmlFor="proveedor" className="px-2 text-gray-500" required>
                                        Proveedor
                                    </Label>
                                    <Combobox
                                        items={proveedores}
                                        placeholder="Selec. proveedor..."
                                        classNames="w-60 p-6"
                                        isActive={true}
                                        idSelect={tracking.idProveedor}
                                    />
                                </div>

                                <div className={'w-[50%] flex flex-col gap-2'}>
                                    <Label htmlFor="cliente" className="px-2 text-gray-500" required>
                                        Cliente
                                    </Label>
                                    <Combobox
                                        items={proveedores}
                                        placeholder="Selec. cliente..."
                                        classNames="w-60 p-6"
                                        isActive={true}
                                        idSelect={tracking.idCliente}
                                    />
                                </div>

                            </div>

                            <div className="w-[100%] flex flex-col gap-2">
                                <Label htmlFor="direcion" className="px-2 text-gray-500" required>
                                    Dirección
                                </Label>
                                <Combobox
                                    items={proveedores}
                                    placeholder="Selec. dirección..."
                                    classNames="w-60 p-6"
                                    isActive={true}
                                    idSelect={tracking.idCliente}
                                />
                            </div>

                            <div className="relative w-[100%] pb-2">
                                <InputFloatingLabel id="observaciones" type="text" value={tracking.observaciones} label="Observaciones" classNameContainer={'w-full'} />
                            </div>


                        </CardContent>
                    </Card>
                </div>
            </MainContainer>
        </AppLayout>
    );
}
