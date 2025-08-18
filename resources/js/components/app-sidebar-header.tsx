import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Buttons } from '@/ownComponents/buttons';
import { type BreadcrumbItem as BreadcrumbItemType, ButtonHeader } from '@/types';

export function AppSidebarHeader({ breadcrumbs = [], buttons = [] }: { breadcrumbs?: BreadcrumbItemType[]; buttons?: ButtonHeader[] }) {
    return (
        <header className="flex h-16 shrink-0 gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex w-full items-center justify-between gap-2">
                <div className="flex w-full items-center justify-between gap-2">
                    <div className="flex flex-shrink items-center gap-2 pe-3">
                        <SidebarTrigger className="-ml-1" />
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>

                    <Buttons
                        buttons={buttons}
                        classNameContainer="flex flex-nowrap gap-2 flex-1 justify-end min-w-10 md:min-w-20 lg:min-w-70 xl:min-w-70"
                    />
                </div>
            </div>
        </header>
    );
}
