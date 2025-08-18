export type { TagifySettings, TagifyInstance } from '@yaireo/tagify';

export interface TagifyTag {
    value: string;
    id?: number;
    [key: string]: any;
}
