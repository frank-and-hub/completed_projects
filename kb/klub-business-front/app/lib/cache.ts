const memoryCache = new Map();

export function setCache(key: string, data: any, ttl: number = 60) {
  const expires = Date.now() + ttl * 1000;
  if(!key.includes('List')) memoryCache.set(key, { data, expires });
}

export function getCache(key: string) {
  const cached = memoryCache.get(key);
  if (!cached) return null;
  if (Date.now() > cached.expires) {
    memoryCache.delete(key);
    return null;
  }
  return cached.data;
}
