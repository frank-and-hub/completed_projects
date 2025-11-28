import { Processor, Process, OnQueueFailed } from '@nestjs/bull';
import { Job } from 'bullmq';

@Processor('email')
export class EmailProcessor {
  @Process('send-welcome') 
  async handleSendWelcome(job: Job) {
    console.log('Sending welcome email to:', job.data.email);

    await new Promise((res) => setTimeout(res, 3000));

    console.log(' Email sent successfully to:', job.data.email);
  }

  @OnQueueFailed()
  handleFailed(job: Job, error: any) {
    console.error(` Job "${job.name}" failed:`, error);
  }
}
