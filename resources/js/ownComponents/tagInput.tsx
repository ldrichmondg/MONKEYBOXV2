import React, { useRef, useEffect, forwardRef, useImperativeHandle } from 'react';
import Tagify, { TagifyBaseTagData } from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import '../../css/overrideTagify.css'
import { TagifyTag } from '@/types';

export interface TagifySettings<T extends TagifyBaseTagData = TagifyBaseTagData> {
    whitelist?: T[] | string[];
    blacklist?: T[] | string[];
    placeholder?: string;
    delimiters?: string;
    pattern?: string | RegExp;
    mode?: 'select' | 'mix';
    duplicates?: boolean;
    enforceWhitelist?: boolean;
    autoComplete?: boolean | { enabled: boolean };
    dropdown?: {
        enabled?: number;
        maxItems?: number;
        closeOnSelect?: boolean;
        highlightFirst?: boolean;
    };
    templates?: {
        tag?: (tagData: T) => string;
        dropdownItem?: (itemData: T) => string;
    };
    transformTag?: (tagData: T) => T;
    validate?: (tagData: T) => boolean | string;
    editTags?: boolean;
    callbacks?: {
        [key: string]: (event: CustomEvent) => void;
    };
}

export interface TagifyInstance<T extends TagifyBaseTagData = TagifyBaseTagData> {
    addTags: (tags: T[] | string[], clearAfterAdd?: boolean) => void;
    removeAllTags: () => void;
    destroy: () => void;
    on: (event: string, callback: (event: CustomEvent) => void) => void;
    off: (event: string, callback?: (event: CustomEvent) => void) => void;
    get whitelist(): T[];
    set whitelist(whitelist: T[] | string[]);
    value: T[] | string[];
    DOM: {
        input: HTMLInputElement;
        scope: HTMLElement;
    };
}


export type TagifyRef = {
    getInstance: () => TagifyInstance<TagifyTag> | null;
    addTags: (tags: TagifyTag[] | string[]) => void;
    removeAllTags: () => void;
};

interface TagInputProps {
    value?: TagifyTag[] | string[];
    onChange?: (tags: TagifyTag[]) => void;
    settings?: TagifySettings<TagifyTag>;
    className?: string;
    placeholder?: string;
    whitelist?: TagifyTag[] | string[];
    onAdd?: (tag: TagifyTag) => void;
    onRemove?: (tag: TagifyTag) => void;
    isActive?: boolean;
}

const TagInput = forwardRef<TagifyRef, TagInputProps>(
    (
        {
            value = [],
            onChange,
            settings = {},
            className = '',
            placeholder = 'SSS',
            whitelist = [],
            isActive = true
        },
        ref
    ) => {
        const inputRef = useRef<HTMLInputElement>(null);
        const tagifyRef = useRef<TagifyInstance<TagifyTag> | null>(null);

        // Exponer métodos al componente padre
        useImperativeHandle(ref, () => ({
            getInstance: () => tagifyRef.current,
            addTags: (tags) => tagifyRef.current?.addTags(tags),
            removeAllTags: () => tagifyRef.current?.removeAllTags(),
        }));

        // Inicializar Tagify
        useEffect(() => {

            const inputEl = inputRef.current;
            if (!inputEl) return;

            // 🔐 Si ya estaba tagificado, destrúyelo primero
            if ((inputEl as any)._tagify) {
                //(inputEl as any)._tagify.destroy(); odio que no me sirva, llevo 3h intentandolo y nada
            }

            if (!inputRef.current) return;

            const tagify = new Tagify(inputRef.current, {
                placeholder,
                whitelist,
                dropdown: {
                    enabled: 0,
                    maxItems: 20,
                    closeOnSelect: false,
                },
                ...settings,
            });

            tagify.setReadonly(!isActive); // Evita edición
            tagifyRef.current = tagify;

            // Event handlers
            const onChangeF = (e: CustomEvent) => {
                const tags = e.detail.value ? JSON.parse(e.detail.value) : [];
                onChange?.(tags);

                if(onChange) onChange(tags);

            };

            tagify.on('change', onChangeF);

            return () => {
                // Limpiar event listeners primero
                tagify.off('change', onChangeF);

                // Verificar si la instancia existe antes de destruir
                if (
                    tagifyRef.current &&
                    typeof tagifyRef.current.destroy === 'function' &&
                    tagifyRef.current.DOM &&
                    tagifyRef.current.DOM.input instanceof HTMLInputElement
                ) {
                    try {
                        console.log("Intenta destruir")
                        tagifyRef.current.destroy();
                    } catch (error) {
                        console.warn('Error al destruir Tagify:', error);
                    }
                }
                tagifyRef.current = null;
            };
        }, [onChange, placeholder, settings, whitelist, isActive]);



        return (
            <input
                ref={inputRef}
                className={`tagify ${className}`}
                placeholder={placeholder}
                defaultValue={Array.isArray(value) ? value.map(tag => typeof tag === 'string' ? tag : tag.value).join(',') : ''}
                disabled={!isActive}
            />
        );
    }
);

TagInput.displayName = 'TagInput';

export default TagInput;
