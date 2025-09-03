import type { ReactNode } from 'react';
interface MainContainerProps {
    children: ReactNode;
    className?: string;
}

export function MainContainer({ className = "" , children }: MainContainerProps) {
    return (
        <div className={"flex h-100 justify-center " + className} >
            <div className="w-[97%]">
                {children}
            </div>
        </div>
    );
}
