import { Test, TestingModule } from '@nestjs/testing';
import { BusinessStaticPageService } from './business-static-page.service';

describe('BusinessStaticPageService', () => {
  let service: BusinessStaticPageService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [BusinessStaticPageService],
    }).compile();

    service = module.get<BusinessStaticPageService>(BusinessStaticPageService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
