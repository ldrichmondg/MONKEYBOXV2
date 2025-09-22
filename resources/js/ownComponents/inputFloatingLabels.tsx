import { Label } from '@/components/ui/label'; // shadcn Label
import React from 'react';
import { Input } from '@/components/ui/input';
import { InputError } from '@/types/input';


interface InputFloatingLabelProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label: string;
    required?: boolean;
    id: string;
    className?: string;
    classNameContainer?: string;
    error: InputError | undefined;
}

export default function InputFloatingLabel({
                                               id,
                                               label,
                                               required,
                                               className = "",
                                               classNameContainer = "",
                                               error = undefined,
                                               ...props
                                           }: InputFloatingLabelProps) {
    return (
        <div className={"relative " + classNameContainer }>
            <input
                id={id}
                placeholder=" "
                className={`peer block w-full rounded-md border border-gray-100 bg-transparent px-3 pt-7 pb-2 text-sm focus:border-orange-400 focus:ring focus:ring-orange-200 focus:outline-none ${className} ${!!error?.message && 'border-red-400'}`}
                {...props}
            />
            <p className='text-red-400'>{error?.message}</p>

            <Label
                htmlFor={id}
                className="absolute top-2 left-3 text-sm transition-all duration-200 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-2 peer-focus:text-sm peer-focus:text-gray-500"
            >
                {label} {required && <span className="text-red-400">*</span>}
            </Label>
        </div>
    );
};
