import { Controller, Get, Post, Body, Patch, Param, Delete, Query, ParseUUIDPipe } from '@nestjs/common';
import { BusinessStaticPageService } from './business-static-page.service';
import { CreateBusinessStaticPageDto } from './dto/create-business-static-page.dto';
import { UpdateBusinessStaticPageDto } from './dto/update-business-static-page.dto';
import { QueryDto } from 'src/auth/dto';
import { BusinessStaticPageEntity } from './entities';

@Controller({ path: 'business-static-page', version: '1' })
export class BusinessStaticPageController {
  constructor(private readonly businessStaticPageService: BusinessStaticPageService) { }

  @Post()
  create(@Body() createBusinessStaticPageDto: CreateBusinessStaticPageDto): Promise<BusinessStaticPageEntity> {
    return this.businessStaticPageService.create(createBusinessStaticPageDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.businessStaticPageService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<BusinessStaticPageEntity> {
    return this.businessStaticPageService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateBusinessStaticPageDto: UpdateBusinessStaticPageDto): Promise<BusinessStaticPageEntity> {
    return this.businessStaticPageService.update(id, updateBusinessStaticPageDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.businessStaticPageService.remove(id);
  }
}
