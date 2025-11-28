import { Controller, Get, Post, Body, Patch, Param, Delete, Query, ParseUUIDPipe, UseGuards } from '@nestjs/common';
import { BusinessService } from './business.service';
import { CreateBusinessDto, UpdateBusinessDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { BusinessEntity } from './entities';
import { JwtAuthGuard, BusinessOwnerGuard } from 'src/auth/guards';

@Controller({ path: 'business', version: '1' })
@UseGuards(JwtAuthGuard)
export class BusinessController {
  constructor(private readonly businessService: BusinessService) { }

  @Post()
  create(@Body() createBusinessDto: CreateBusinessDto): Promise<BusinessEntity> {
    return this.businessService.create(createBusinessDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.businessService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<BusinessEntity> {
    return this.businessService.findOne(id);
  }

  @Patch(':id')
  @UseGuards(BusinessOwnerGuard)
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateBusinessDto: UpdateBusinessDto): Promise<BusinessEntity> {
    return this.businessService.update(id, updateBusinessDto);
  }

  @Delete(':id')
  @UseGuards(BusinessOwnerGuard)
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.businessService.remove(id);
  }

  @Get('user/:userId')
  getUserBusinesses(@Param('userId', ParseUUIDPipe) userId: string) {
    return this.businessService.getUserBusinesses(userId);
  }

  @Get(':id/owners')
  getOwners(@Param('id', ParseUUIDPipe) id: string) {
    return this.businessService.getOwners(id);
  }

  @Get(':id/employees')
  getEmployees(@Param('id', ParseUUIDPipe) id: string) {
    return this.businessService.getEmployees(id);
  }

  @Post(':id/owners')
  @UseGuards(BusinessOwnerGuard)
  addOwner(@Param('id', ParseUUIDPipe) id: string, @Body() body: { userId: string }) {
    return this.businessService.addOwner(id, body.userId);
  }

  @Delete(':id/owners/:userId')
  @UseGuards(BusinessOwnerGuard)
  removeOwner(@Param('id', ParseUUIDPipe) id: string, @Param('userId', ParseUUIDPipe) userId: string) {
    return this.businessService.removeOwner(id, userId);
  }
}
