import { Option } from "@/types/CommonQueryParams";
import { toast } from "react-toastify";

export const is = {
  str: (v: unknown): v is string => typeof v === 'string',
  num: (v: unknown): v is number => typeof v === 'number',
  bool: (v: unknown): v is boolean => typeof v === 'boolean',
  obj: (v: unknown): v is object => typeof v === 'object' && v !== null,
  arr: (v: unknown): v is unknown[] => Array.isArray(v),
  nil: (v: unknown): v is null | undefined => v == null,
}

export const wait = (ms: number) => new Promise(res => setTimeout(res, ms));

export const pick = <T extends object, K extends keyof T>(obj: T, keys: K[]): Pick<T, K> => Object.fromEntries(keys.map(k => [k, obj[k]])) as Pick<T, K>;

export const slugify = (str: string): string => str.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

export const uuid = () => Math.random().toString(36).slice(2, 10);

export const cn = (...classes: (string | false | null | undefined)[]) => classes.filter(Boolean).join(' ');

export const clamp = (value: number, min: number, max: number) => Math.min(Math.max(value, min), max);

export const rand = (min: number, max: number) => Math.floor(Math.random() * (max - min + 1)) + min;

export const cap = (str: string) => str.charAt(0).toUpperCase() + str.slice(1);

export const sleep = (ms: number) => new Promise((res) => setTimeout(res, ms));

export const trimAll = <T extends Record<string, string>>(obj: T): T =>
  Object.fromEntries(
    Object.entries(obj).map(([k, v]) => [k, v.trim()])
  ) as T;

export const truncate = (str: string | undefined | null, len: number) => str ? str.length > len ? str.slice(0, len) + '…' : str : `No Data`;

export const uniq = <T>(arr: T[]) => [...new Set(arr)];

export const chunk = <T>(arr: T[], size: number): T[][] => {
  const result: T[][] = [];
  for (let i = 0; i < arr.length; i += size) {
    result.push(arr.slice(i, i + size));
  }
  return result;
}

export const isEmpty = (val: unknown): boolean =>
  val == null || (Array.isArray(val) || typeof val === 'string') ? val?.length === 0 :
    typeof val === 'object' ? Object.keys(val).length === 0 :
      false;

export const omit = <T extends object, K extends keyof T>(obj: T, keys: K[]): Omit<T, K> =>
  Object.fromEntries(Object.entries(obj).filter(([k]) => !keys.includes(k as K))) as Omit<T, K>;

export const copy = async (text: string) => {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch {
      return false;
    }
  }
  return false;
}

export const now = () => Date.now();

export const fmtDate = (d: Date | string | number) =>
  new Date(d).toISOString().split('T')[0];

export const ago = (d: Date | string | number) => {
  const diff = Date.now() - new Date(d).getTime();
  const s = Math.floor(diff / 1000);
  const m = Math.floor(s / 60);
  const h = Math.floor(m / 60);
  const d_ = Math.floor(h / 24);

  if (d_ > 0) return `${d_}d ago`;
  if (h > 0) return `${h}h ago`;
  if (m > 0) return `${m}m ago`;
  return `${s}s ago`;
}

export const pipe = <T>(...fns: Array<(x: T) => T>) => (x: T) => fns.reduce((v, f) => f(v), x);

export const debounce = <T extends (...args: any[]) => void>(fn: T, delay: number = 1000) => {
  let timer: ReturnType<typeof setTimeout>;
  return (...args: Parameters<T>) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

export const throttle = <T extends (...args: any[]) => void>(fn: T, limit: number = 60) => {
  let inThrottle = false;
  return (...args: Parameters<T>) => {
    if (!inThrottle) {
      fn(...args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
};

export const get18YearsAgoDate = (): string => {
  const today = new Date();
  today.setFullYear(today.getFullYear() - 18); // subtract 18 years
  return today.toISOString().split('T')[0]; // format as YYYY-MM-DD
}

export const getTodayDate = (): string => {
  const today = new Date();
  return today.toISOString().split('T')[0]; // format as YYYY-MM-DD
}

export const getTomorrowDate = (): string => {
  const today = new Date();
  today.setDate(today.getDate() + 1); // add 1 day
  return today.toISOString().split('T')[0]; // format as YYYY-MM-DD
}

export const getUniqueOptions = (options: Option[]): Option[] => {
  return Array.from(new Map(options.map(o => [o.value, o])).values());
}

export const tostingError = (status: number) => {
  switch (status) {
    case 400:
      toast.error('Bad request');
      break;
    case 401:
      toast.error('Unauthorized – redirecting to login');
      break;
    case 403:
      toast.warning('Forbidden – access denied');
      break;
    case 404:
      toast.error('Not found – redirecting...');
      break;
    case 405:
      toast.error('Method Not Allowed');
      break;
    case 406:
      toast.error('Not Acceptable');
      break;
    case 407:
      toast.error('Proxy Authentication Required');
      break;
    case 408:
      toast.error('Request Timeout');
      break;
    case 409:
      toast.error('Conflict');
      break;
    case 429:
      toast.error('Too Many Requests');
      break;
    case 500:
      toast.error('Internal Server Error');
      break;
    case 501:
      toast.error('Not Implemented');
      break;
    case 502:
      toast.error('Bad Gateway');
      break;
    case 503:
      toast.error('Service Unavailable');
      break;
    case 504:
      toast.error('Gateway Timeout');
      break;
    case 505:
      toast.error('HTTP Version Not Supported');
      break;
    default:
      toast.error('Something went wrong!');
      break;
  }
}

export const sanitizePatchData = (data: Record<string, any>, allowedFields: string[]) => {
  return Object.fromEntries(
    Object.entries(data).filter(
      ([key, value]) => allowedFields.includes(key) && value !== null && value !== undefined
    )
  );
}

export const sanitizeFormData = (formData: any): any => {
  return Object.fromEntries(
    Object.entries(formData).filter(
      ([_, value]) => value !== '' && value !== null && value !== undefined
    )
  );
}