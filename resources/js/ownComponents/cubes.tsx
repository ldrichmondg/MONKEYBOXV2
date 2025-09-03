import { LucideIcon } from 'lucide-react';


interface Props {
    icon?: LucideIcon;
    name?: string;
    className?: string;
    hijoClassName?: string;
    }

export default function Cubes({
                                  icon,
                                  name,
                                  className = "",
                                  hijoClassName,
                              }: Props) {
    return (
        <div className={`flex items-center justify-center rounded-lg text-sidebar-primary-foreground ${className}`}>
            {icon && (
                <Icon className={`dark:text-black ${hijoClassName}`} />

            )}
            {name && (
                <span className={`${hijoClassName}`}>{name}</span>
            )}
        </div>
    );
}
