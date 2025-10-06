import { Button } from '@/components/ui/button';
import { ButtonHeader } from '@/types';
import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import * as React from 'react';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export function Buttons({ buttons = [], classNameContainer = '' }: { buttons?: ButtonHeader[], classNameContainer?: string }) {
    const containerRef = React.useRef<HTMLDivElement>(null);
    const [hasOverflow, setHasOverflow] = React.useState(false);
    const buttonsWidth = React.useRef<number>(0);
    React.useEffect(() => {
        const observer = new ResizeObserver(() => {
            const el = containerRef.current;
            if (!el) return;

            if(el.scrollWidth > el.clientWidth) buttonsWidth.current = el.scrollWidth;

            setHasOverflow(buttonsWidth.current > el.clientWidth);
        });

        if (containerRef.current) {
            observer.observe(containerRef.current);
        }

        return () => observer.disconnect();
    }, []);

    const buttonsHtml = buttons.map((item,index) => (
        <Button
            key={index + "-button"}
            id={index + "-button"}
            className={item.className}
            size="sm"
            disabled={!item.isActive}
            onClick={item.onClick}
        >
            {item.href ? (
                <Link href={item.href} prefetch>
                    {item.name}
                </Link>
            ) : (
                item.name
            )}
            {item.icon && <item.icon />}
        </Button>
    ));

    const buttonsDropdownHtml = buttons.map((item,index) => (
        <Button
            key={index + '-buttonDropdown'}
            id={index + '-buttonDropdown'}
            className={item.className + ' w-full justify-start'}
            size="sm"
            disabled={!item.isActive}
            onClick={item.onClick}
        >
            {item.href ? (
                <Link href={item.href} prefetch>
                    {item.name}
                </Link>
            ) : (
                item.name
            )}
            {item.icon && <item.icon />}
        </Button>
    ));

    return (
        <div className={classNameContainer} >
            <div ref={containerRef} className="flex gap-2 overflow-hidden w-full justify-end">
                {hasOverflow ? (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline">
                                <Menu />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56">
                            <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <div className="flex flex-col gap-2">
                                {buttonsDropdownHtml}
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>
                ) : (
                    buttonsHtml
                )}
            </div>
        </div>
    );

}
