import { Module } from '@nestjs/common';
import { CommonDataService } from './common_data.service';
import { CommonDataController } from './common_data.controller';

@Module({
  controllers: [CommonDataController],
  providers: [CommonDataService],
})
export class CommonDataModule {}
