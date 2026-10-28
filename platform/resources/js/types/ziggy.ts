import { Config } from 'ziggy-js';

// Route params are loosely typed: Ziggy accepts ids, arrays and objects alike.
type Params = string | number | (string | number)[] | Record<string, unknown>;

declare global {
    function route(): Config;
    function route(name: string, params?: Params, absolute?: boolean): string;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
    }
}
