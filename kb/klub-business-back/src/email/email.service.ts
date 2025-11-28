import { Injectable } from '@nestjs/common';
import { InjectQueue } from '@nestjs/bull';
import { Queue } from 'bullmq';

@Injectable()
export class EmailService {
  constructor(@InjectQueue('email') private readonly emailQueue: Queue) {}

  async sendWelcomeEmail(user: any) {
    console.log('📥 Queuing email job...');
    await this.emailQueue.add('send-welcome', user, {
      delay: 5000, // optional delay in ms
      attempts: 3, // retry up to 3 times
      backoff: 2000, // wait 2s between retries
    });
  }
}
