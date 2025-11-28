import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query, ParseUUIDPipe } from '@nestjs/common';
import { BusinessCategoryService } from './business-category.service';
import { CreateBusinessCategoryDto, UpdateBusinessCategoryDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { BusinessCategoryEntity } from './entities';
import { JwtGuard, RolesGuard } from 'src/auth/guards';
import { Roles } from 'src/auth/decorator';

@UseGuards(JwtGuard, RolesGuard)
@Roles('Supper Admin')
@Controller({ path: 'business-category', version: '1' })
export class BusinessCategoryController {
  constructor(private readonly businessCategoryService: BusinessCategoryService) { }

  @Post()
  create(@Body() createBusinessCategoryDto: CreateBusinessCategoryDto): Promise<BusinessCategoryEntity> {
    return this.businessCategoryService.create(createBusinessCategoryDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.businessCategoryService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<BusinessCategoryEntity> {
    return this.businessCategoryService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateBusinessCategoryDto: UpdateBusinessCategoryDto): Promise<BusinessCategoryEntity> {
    return this.businessCategoryService.update(id, updateBusinessCategoryDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.businessCategoryService.remove(id);
  }
}
