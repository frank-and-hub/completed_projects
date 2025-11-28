import { Module } from '@nestjs/common';
import { PaymentService } from './payment.service';
import { RedisService } from 'src/redis/redis.service';

@Module({
    providers: [PaymentService, RedisService],
    exports: [PaymentService]
})
export class PaymentModule { }
