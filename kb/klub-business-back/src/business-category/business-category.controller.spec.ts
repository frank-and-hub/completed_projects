import { Test, TestingModule } from '@nestjs/testing';
import { BusinessCategoryController } from './business-category.controller';
import { BusinessCategoryService } from './business-category.service';

describe('BusinessCategoryController', () => {
  let controller: BusinessCategoryController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [BusinessCategoryController],
      providers: [BusinessCategoryService],
    }).compile();

    controller = module.get<BusinessCategoryController>(BusinessCategoryController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
