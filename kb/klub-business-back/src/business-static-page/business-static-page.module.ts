import { Module } from '@nestjs/common';
import { BusinessStaticPageService } from './business-static-page.service';
import { BusinessStaticPageController } from './business-static-page.controller';

@Module({
  controllers: [BusinessStaticPageController],
  providers: [BusinessStaticPageService],
})
export class BusinessStaticPageModule {}
