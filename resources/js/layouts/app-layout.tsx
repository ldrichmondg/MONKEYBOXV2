import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem, ButtonHeader } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    buttons?: ButtonHeader[];
}

export default ({ children, breadcrumbs, buttons, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate breadcrumbs={breadcrumbs} buttons={buttons} {...props}>
        {children}
    </AppLayoutTemplate>
);
