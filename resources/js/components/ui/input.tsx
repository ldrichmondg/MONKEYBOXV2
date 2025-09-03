import * as React from 'react';

import { cn } from '@/lib/utils';
import { InputError } from '@/types/input';

interface InputProps extends React.ComponentProps<'input'> {
    error?: InputError;
}

function Input({ className, type, error, ...props }: InputProps) {
    const { readOnly } = props;
    const readOnlyClases = ' border-0 focus-visible:!ring-0 focus-visible:!border-transparent !cursor-text !select-text shadow-none'
    className = readOnly ? className + readOnlyClases : className;

    return (
        <>
            <input
                type={type}
                data-slot="input"
                name={error?.name}
                className={cn(
                    'border-input file:text-foreground placeholder:text-muted-foreground selection:bg-white-200 selection:text-primary-foreground flex h-9 w-full min-w-0 rounded-md border bg-white px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                    !!error?.message && 'border-red-400',
                    className
                )}
                {...props}
            />
            <p className='text-red-400'>{error?.message}</p>
        </>
    );
}

export { Input };
