import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards, ParseUUIDPipe } from '@nestjs/common';
import { CountriesService } from './countries.service';
import { CreateCountryDto, UpdateCountryDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { JwtGuard } from 'src/auth/guards';
import { CountryEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'countries', version: '1' })
export class CountriesController {
  constructor(private readonly countriesService: CountriesService) { }

  @Post()
  create(@Body() createCountryDto: CreateCountryDto): Promise<CountryEntity> {
    return this.countriesService.create(createCountryDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.countriesService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<CountryEntity> {
    return this.countriesService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateCountryDto: UpdateCountryDto): Promise<CountryEntity> {
    return this.countriesService.update(id, updateCountryDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.countriesService.remove(id);
  }
}
