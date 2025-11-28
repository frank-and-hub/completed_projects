import { Injectable, OnModuleDestroy, OnModuleInit } from '@nestjs/common';
import Redis from 'ioredis';

@Injectable()
export class RedisService implements OnModuleInit, OnModuleDestroy {
    private client: Redis;

    onModuleInit() {
        this.client = new Redis({
            host: process.env.REDIS_HOST || 'localhost',
            port: process.env.REDIS_PORT ? parseInt(process.env.REDIS_PORT) : 6379,
        });
    }

    onModuleDestroy() {
        this.client.disconnect();
        this.client.quit();
    }

    async connect() {
        await this.client.connect();
    }

    async set(key: string, value: any, ttlSeconds?: number) {
        const data = typeof value === 'string' ? value : JSON.stringify(value);
        if (ttlSeconds) return this.client.set(key, data, 'EX', ttlSeconds);
        return this.client.set(key, data);
    }

    async get<T = any>(key: string): Promise<T | null> {
        const data = await this.client.get(key);
        try {
            return data ? JSON.parse(data) : null;
        } catch {
            return data as any;
        }
    }

    async del(key: string) {
        return this.client.del(key);
    }
    

    async publish(channel: string, message: any) {
        return this.client.publish(channel, JSON.stringify(message));
    }
    
    async subscribe(channel: string, callback: (msg: any) => void) {
        const subscriber = new Redis({
            host: process.env.REDIS_HOST,
            port: process.env.REDIS_PORT ? parseInt(process.env.REDIS_PORT) : 6379,
        });
        await subscriber.subscribe(channel);
        subscriber.on('message', (chan, message) => {
            if (chan === channel) callback(JSON.parse(message));
        });
    }


}
