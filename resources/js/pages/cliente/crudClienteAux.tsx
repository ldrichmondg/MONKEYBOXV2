import { ConstruccionCodigoPostal } from '@/types/cliente';
import React from 'react';


export function ValidarCamposDireccion(construccionDireccion: ConstruccionCodigoPostal,
                                setConstruccionDireccion: React.Dispatch<React.SetStateAction<ConstruccionCodigoPostal>>){
    // 1. Se valida cada campo de la construccion de direccion
    // 2. si hay un error se retorna true, sino false
    let hayError = false;

    if (construccionDireccion.idProvincia === -1) {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'idProvincia', message: 'El campo provincia está vacío' }
            ],
        }));

        hayError = true;
    }

    if (construccionDireccion.idCanton === -1) {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'idCanton', message: 'El campo cantón está vacío' }
            ],
        }));
        hayError = true;
    }

    if (construccionDireccion.idDistrito === -1) {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'idDistrito', message: 'El campo distrito está vacío' }
            ],
        }));
        hayError = true;
    }

    if (construccionDireccion.codigoPostal === 0) {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'codigoPostal', message: 'El campo código postal está vacío' }
            ],
        }));
        hayError = true;
    }

    if (construccionDireccion.direccion === '') {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'direccion', message: 'El campo dirección está vacío' }
            ],
        }));
        hayError = true;
    }

    if (construccionDireccion.tipoDireccion === -1) {
        setConstruccionDireccion((prev) => ({
            ...prev,
            errores: [
                ...(prev.errores || []),
                { name: 'tipoDireccion', message: 'El campo tipo de dirección está vacío' }
            ],
        }));
        hayError = true;
    }

    return hayError;

}
