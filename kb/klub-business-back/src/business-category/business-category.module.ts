import { Module } from '@nestjs/common';
import { BusinessCategoryService } from './business-category.service';
import { BusinessCategoryController } from './business-category.controller';

@Module({
  controllers: [BusinessCategoryController],
  providers: [BusinessCategoryService],
})
export class BusinessCategoryModule {}
