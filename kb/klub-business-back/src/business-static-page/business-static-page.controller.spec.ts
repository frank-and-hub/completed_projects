import { Test, TestingModule } from '@nestjs/testing';
import { BusinessStaticPageController } from './business-static-page.controller';
import { BusinessStaticPageService } from './business-static-page.service';

describe('BusinessStaticPageController', () => {
  let controller: BusinessStaticPageController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [BusinessStaticPageController],
      providers: [BusinessStaticPageService],
    }).compile();

    controller = module.get<BusinessStaticPageController>(BusinessStaticPageController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
