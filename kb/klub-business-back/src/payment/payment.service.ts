import { Injectable } from '@nestjs/common';
import { RedisService } from 'src/redis/redis.service';

@Injectable()
export class PaymentService {
    constructor(private readonly redis: RedisService) { }

    async processPayment(userId: number, amount: number) {
        const cacheKey = `payment:${userId}`;
        const cached = await this.redis.get(cacheKey);
        if (cached) {
            return { status: 'duplicate', data: cached };
        }

        const result = { success: true, transactionId: Date.now(), amount };

        await this.redis.set(cacheKey, result, 60); // cache 1 min

        await this.redis.publish('payment_events', { userId, amount, result });

        return result;
    }
}
