declare module '@yaireo/tagify' {
    export interface TagifyBaseTagData {
        value: string;
        [key: string]: any;
    }

    export interface TagifyStatic {
        new <T extends TagifyBaseTagData = TagifyBaseTagData>(
            element: HTMLElement,
            settings?: TagifySettings<T>
        ): TagifyInstance<T>;
    }

    const Tagify: TagifyStatic;
    export default Tagify;
}
