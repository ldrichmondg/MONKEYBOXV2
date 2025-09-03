import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { AlertCircleIcon } from 'lucide-react';
import React from 'react';


interface alertErrorProps {
    flash?: {
        success?: string;
        error?: string;
    };
}
export default function AlertError({flash} : alertErrorProps){

    return (
        <>
        {flash?.error && (
            <Alert variant="destructive" className={'mt-2'}>
                <AlertCircleIcon />
                <AlertTitle>Hubo un error al navegar a consulta tracking</AlertTitle>
                <AlertDescription>
                    <p> No se encontró el tracking asociado.</p>

                </AlertDescription>
            </Alert>
        )}
        </>
    );
}
