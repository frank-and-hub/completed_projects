import { Controller, Get, Query, UseInterceptors } from '@nestjs/common';
import { CommonDataService } from './common_data.service';
import { QueryDto } from 'src/common_data/dto/query.dto';
import { CacheInterceptor, CacheTTL } from '@nestjs/cache-manager';

@UseInterceptors(CacheInterceptor)
@Controller({ path: 'common-data', version: '1' })
export class CommonDataController {
  constructor(private readonly commonDataService: CommonDataService) { }

  @CacheTTL(60 * 1000)
  @Get()
  findOne(@Query() query: QueryDto) {
    return this.commonDataService.findOne(query);
  }
}
