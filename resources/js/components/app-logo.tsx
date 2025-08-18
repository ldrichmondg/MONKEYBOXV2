import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex rounded-sm size-8 items-center justify-center bg-red-400 text-sidebar-primary-foreground px-2">
                <AppLogoIcon className="size-5 fill-current text-red-400 dark:text-black" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">MonkeyBox</span>
            </div>
        </>
    );
}
