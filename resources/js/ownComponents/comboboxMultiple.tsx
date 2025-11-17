'use client'

import { useId, useState } from 'react'
import { CheckIcon, ChevronsUpDownIcon, XIcon } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList
} from '@/components/ui/command'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { ComboBoxItem } from '@/types'

export function ComboboxMultiple({
                                     items = [],
                                     onChange,
                                     ...props
                                 }: React.HTMLAttributes<HTMLDivElement> & {
    items: ComboBoxItem[],
    onChange?: (values: string[]) => void
}) {
    const { className, ...rest } = props

    const id = useId()
    const [open, setOpen] = useState(false)
    const [expanded, setExpanded] = useState(false)

    const [selectedValues, setSelectedValues] = useState<string[]>([])

    const toggleSelection = (value: string) => {
        setSelectedValues(prev => {
            const newValues = prev.includes(value)
                ? prev.filter(v => v !== value)
                : [...prev, value]

            onChange?.(newValues)   // ⬅️ SE NOTIFICA AL PADRE
            return newValues
        })
    }

    const removeSelection = (value: string) => {
        setSelectedValues(prev => {
            const newValues = prev.filter(v => v !== value)
            onChange?.(newValues)   // ⬅️ TAMBIÉN AQUÍ
            return newValues
        })
    }

    const maxShownItems = 2
    const visibleItems = expanded ? selectedValues : selectedValues.slice(0, maxShownItems)
    const hiddenCount = selectedValues.length - visibleItems.length

    return (
        <div
            className={`w-full max-w-xs space-y-2 ${className || ''}`}
            {...rest}
        >
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        id={id}
                        variant='outline'
                        role='combobox'
                        aria-expanded={open}
                        className='h-auto min-h-8 w-full justify-between hover:bg-transparent'
                    >
                        <div className='flex flex-wrap items-center gap-1 pr-2.5'>
                            {selectedValues.length > 0 ? (
                                <>
                                    {visibleItems.map(val => {
                                        const item = items.find(c => c.id.toString() === val.toString())

                                        return item ? (
                                            <Badge key={val} variant='outline'>
                                                {item.descripcion}
                                                <Button
                                                    variant='ghost'
                                                    size='icon'
                                                    className='size-4'
                                                    onClick={e => {
                                                        e.stopPropagation()
                                                        removeSelection(val)
                                                    }}
                                                    asChild
                                                >
                                                    <span>
                                                        <XIcon className='size-3' />
                                                    </span>
                                                </Button>
                                            </Badge>
                                        ) : null
                                    })}

                                    {hiddenCount > 0 || expanded ? (
                                        <Badge
                                            variant='outline'
                                            onClick={e => {
                                                e.stopPropagation()
                                                setExpanded(prev => !prev)
                                            }}
                                        >
                                            {expanded ? 'Show Less' : `+${hiddenCount} more`}
                                        </Badge>
                                    ) : null}
                                </>
                            ) : (
                                <span className='text-muted-foreground'></span>
                            )}
                        </div>

                        <ChevronsUpDownIcon size={16} className='text-muted-foreground/80 shrink-0' />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className='w-(--radix-popper-anchor-width) p-0'>
                    <Command>
                        <CommandInput placeholder=''/>
                        <CommandList>
                            <CommandEmpty>No se encontraron items.</CommandEmpty>
                            <CommandGroup>
                                {items.map(item => (
                                    <CommandItem
                                        key={item.id}
                                        value={item.descripcion}
                                        onSelect={() => toggleSelection(item.id.toString())}
                                    >
                                        <span className='truncate'>{item.descripcion}</span>
                                        {selectedValues.includes(item.id.toString()) && (
                                            <CheckIcon size={16} className='ml-auto' />
                                        )}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    )
}
