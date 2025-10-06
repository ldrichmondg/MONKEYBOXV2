import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { MainContainer } from '@/ownComponents/containers/mainContainer';
import InputFloatingLabel from '@/ownComponents/inputFloatingLabels';
import { type BreadcrumbItem, ButtonHeader } from '@/types';
import { Usuario, UsuarioCompleto } from '@/types/usuario';
import { Head } from '@inertiajs/react';
import React from 'react';
import { TrackingCompleto } from '@/types/tracking';
import { Spinner } from '@/ownComponents/spinner';
import { ActualizarUsuario, RegistrarUsuario } from '@/api/usuario/usuario';
import { ErrorModal } from '@/ownComponents/modals/errorModal';
import { ExitoModal } from '@/ownComponents/modals/exitoModal';



export default function RegistroUsuario() {
    const [registrando, setRegistrando] = React.useState<boolean>(false);
    const [usuarioFront, setUsuarioFront] = React.useState<UsuarioCompleto>({
        id: -1,
        nombre: '',
        apellidos: '',
        empresa: '',
        telefono: null,
        correo: '',
        cedula: null,
        errores: []
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Consulta Usuarios',
            href: route('usuario.usuario.consulta.vista'),
        },
        {
            title: 'Registrar Usuario',
            href: route('usuario.usuario.registro.vista'),
        },
    ];

    const buttons: ButtonHeader[] = [
        {
            id: 'registro',
            name: 'Registrar Usuario',
            className: 'bg-red-400 text-white hover:bg-red-500 ',
            isActive: true,
            onClick: () => RegistrararUsuarioAux(usuarioFront, setUsuarioFront, setRegistrando)
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs} buttons={buttons}>
            <Head title={usuarioFront.nombre} />
            <MainContainer>
                <div className="flex flex-row justify-center py-3">
                    <Card className="flex h-auto w-[95%] max-w-[95%] md:w-[80%] md:max-w-[80%] flex-col p-0">
                        <CardHeader className={'flex w-[100%] flex-row items-center justify-between border-b-2 border-gray-100 p-4'}>
                            <p className="text-md font-bold">Usuario</p>
                        </CardHeader>

                        <CardContent className={'grid grid-cols-1 justify-between px-4 pt-0 pb-5 gap-3 md:grid-cols-2 xl:grid-cols-3'}>
                            <InputFloatingLabel
                                id="nombre"
                                type="text"
                                label="Nombre"
                                value={usuarioFront.nombre}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setUsuarioFront((prev) => ({ ...prev, nombre: e.target.value }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'nombre')}
                            />

                            <InputFloatingLabel
                                id="apellidos"
                                type="text"
                                label="Apellidos"
                                value={usuarioFront.apellidos}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setUsuarioFront((prev) => ({ ...prev, apellidos: e.target.value }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'apellidos')}
                            />

                            <InputFloatingLabel
                                id="cedula"
                                type="number"
                                label="Cédula"
                                value={Number(usuarioFront.cedula)}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    const cedula = Number(e.target.value);
                                    setUsuarioFront((prev) => ({ ...prev, cedula: cedula }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'cedula')}
                            />

                            <InputFloatingLabel
                                id="correo"
                                type="text"
                                label="Correo"
                                value={usuarioFront.correo}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setUsuarioFront((prev) => ({ ...prev, correo: e.target.value }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'correo')}
                            />

                            <InputFloatingLabel
                                id="telefono"
                                type="text"
                                label="Teléfono"
                                value={Number(usuarioFront.telefono)}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    const telefono = Number(e.target.value);
                                    setUsuarioFront((prev) => ({ ...prev, telefono: telefono }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'telefono')}
                            />

                            <InputFloatingLabel
                                id="empresa"
                                type="text"
                                label="Empresa"
                                value={usuarioFront.empresa}
                                classNameContainer={'w-[100%]'}
                                required
                                onChange={(e) => {
                                    setUsuarioFront((prev) => ({ ...prev, empresa: e.target.value }));
                                }}
                                error={usuarioFront.errores.find((error) => error.name == 'empresa')}
                            />
                        </CardContent>
                    </Card>
                </div>
            </MainContainer>
            <Spinner isActive={registrando}></Spinner>
        </AppLayout>
    );
}


async function RegistrararUsuarioAux(usuario: UsuarioCompleto, setUsuario: React.Dispatch<React.SetStateAction<UsuarioCompleto>>, setRegistrando: React.Dispatch<React.SetStateAction<boolean>>){
    // 1. Mostrar el registrando para que se muestre el spinner
    // 2. Registrar el usuario
    // 3. Quitar el spinner

    try{
        setRegistrando(true);
        const usuarioRespuesta = await RegistrarUsuario(usuario);
        setUsuario(usuarioRespuesta);
        if (usuarioRespuesta.errores.length == 0) {
            ExitoModal('Registro exitoso', 'Se registró el usuario exitosamente.');
        }

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    }catch (e) {
        ErrorModal('Error al registrar usuario', 'Hubo un error al registrar el usuario. Vuelve a intentarlo o contacta a soporte TI.')
    }finally {
        setRegistrando(false);
    }
}
