"use client"

import * as React from "react"
import { Check, ChevronsUpDown } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover"
import { ComboBoxItem } from '@/types';
import { InputError } from '@/types/input';



export function Combobox( {items = [], placeholder, classNames = '', onChange, isActive, error, idSelect} : {items: ComboBoxItem[], placeholder: string, classNames?: string, onChange?: (idSeleccionado : number) => void, isActive: boolean, error?: InputError, idSelect?: number }) {
    const [open, setOpen] = React.useState(false)
    const [value, setValue] = React.useState('')
    /*asi funcionaba !!error?.message */

    React.useEffect(() => {
        if (idSelect && items.length > 0) {
            const selectedItem = items.find(item => item.id === idSelect)
            if (selectedItem) {
                setValue(selectedItem.descripcion)
            }
        }
    }, [idSelect, items])

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    className={"min-w-[200px] justify-between" + classNames + (error?.message ? ' border-red-400' : '') }
                    disabled={!isActive}
                >
                    {value
                        ? items.find((item) => item.descripcion === value)?.descripcion
                        : placeholder}
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <p className='text-red-400'>{error?.message}</p>
            <PopoverContent className="w-[200px] p-0">
                <Command>
                    <CommandInput placeholder={placeholder} className="h-9" />
                    <CommandList>
                        <CommandEmpty>No framework found.</CommandEmpty>
                        <CommandGroup>
                            {items.map((item) => {
                               return (
                                    <CommandItem
                                        key={item.id}
                                        value={item.descripcion}
                                        onSelect={(currentValue) => {
                                            setValue(currentValue === value ? "" : currentValue)
                                            setOpen(false)

                                            if(onChange) onChange(item.id); // ⬅️ aquí se usa la prop `onChange`
                                        }}
                                    >
                                        {item.descripcion}
                                        <Check
                                            className={cn(
                                                "ml-auto",
                                                value === item.descripcion ? "opacity-100" : "opacity-0"
                                            )}
                                        />
                                    </CommandItem>
                               )
                            })}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    )
}
