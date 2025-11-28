import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards, ParseUUIDPipe } from '@nestjs/common';
import { CitiesService } from './cities.service';
import { CreateCityDto, UpdateCityDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { JwtGuard } from 'src/auth/guards';
import { CityEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'cities', version: '1' })
export class CitiesController {
  constructor(private readonly citiesService: CitiesService) { }

  @Post()
  create(@Body() createCityDto: CreateCityDto): Promise<CityEntity> {
    return this.citiesService.create(createCityDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.citiesService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<CityEntity> {
    return this.citiesService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateCityDto: UpdateCityDto): Promise<CityEntity> {
    return this.citiesService.update(id, updateCityDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.citiesService.remove(id);
  }
}
