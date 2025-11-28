type ConsoleItem = any | Array<string | number | boolean | object | any[]> | string[] | string | object;

export const c = (...args: unknown[]) => console.log(...args);
export const ci = (...args: unknown[]) => console.info(...args);
export const cd = (...args: unknown[]) => console.debug(...args);
export const cw = (...args: unknown[]) => console.warn(...args);
export const ce = (...args: unknown[]) => console.error({ ...args });
export const ct = (...args: unknown[]) => console.table(...args);
export const cl = (label: string, ...args: unknown[]) => console.log(`[${label}]`, ...args);
export const cc = (...args: unknown[]) => { console.clear(); console.log(...args); };
export const cf = (...args: unknown[]) => { console.log(...args); return false; };

export const dir = (obj: unknown) => console.dir(obj, { depth: null });
export const trace = (...args: unknown[]) => console.trace(...args);

export const t = (label: string) => console.time(label);
export const te = (label: string) => console.timeEnd(label);

export const cg = (label: string) => console.group(label);
export const cge = () => console.groupEnd();
