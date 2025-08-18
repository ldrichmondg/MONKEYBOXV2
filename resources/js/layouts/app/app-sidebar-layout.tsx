import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem, ButtonHeader } from '@/types';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({ children, breadcrumbs = [], buttons = [] }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[], buttons?: ButtonHeader[] }>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden ">
                <AppSidebarHeader breadcrumbs={breadcrumbs} buttons={buttons}/>
                {children}
            </AppContent>
        </AppShell>
    );
}
