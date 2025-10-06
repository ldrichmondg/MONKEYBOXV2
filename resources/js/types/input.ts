
export interface InputError {
    name: string;
    message?: string;
}

export interface ErroresInputs {
    errores: InputError[];
}


interface LaravelValidationErrorResponse {
    message: string;
    errors: {
        [field: string]: string[];
    };
}

export function parseLaravelValidationErrors(responseJson: LaravelValidationErrorResponse): InputError[] {
    const errores: InputError[] = [];

    if (responseJson && responseJson.errors) {
        for (const [field, messages] of Object.entries(responseJson.errors)) {
            messages.forEach((msg) => {
                errores.push({
                    name: field,
                    message: msg,
                });
            });
        }
    }

    return errores;
}
