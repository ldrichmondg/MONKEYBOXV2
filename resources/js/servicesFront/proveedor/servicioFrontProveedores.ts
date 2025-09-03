import React from 'react';
import { ComboBoxItem } from '@/types';
import { comboboxProveedor } from '@/api/proveedor/proveedor';

export async function cargarProveedores(setProveedores: React.Dispatch<React.SetStateAction<ComboBoxItem[]>>) {
    const proveedores: ComboBoxItem[] = await comboboxProveedor();
    setProveedores(proveedores);
}
