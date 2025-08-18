import * as React from 'react';

interface SpinnerProps {
    isActive: boolean;
}

export function Spinner({ isActive }: SpinnerProps) {

    return (
        isActive ? (
            <div className="fixed inset-0 bg-orange-100/30 bg-opacity-90 flex items-center justify-center z-50">
                <div className="spinner"></div>

            </div>
        ) : null
    );

}
