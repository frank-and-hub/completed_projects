import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards, ParseUUIDPipe } from '@nestjs/common';
import { CurrencyService } from './currency.service';
import { CreateCurrencyDto, UpdateCurrencyDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { JwtGuard } from 'src/auth/guards';
import { CurrencyEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'currency', version: '1' })
export class CurrencyController {
  constructor(private readonly currencyService: CurrencyService) { }

  @Post()
  create(@Body() createCurrencyDto: CreateCurrencyDto): Promise<CurrencyEntity> {
    return this.currencyService.create(createCurrencyDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.currencyService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<CurrencyEntity> {
    return this.currencyService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateCurrencyDto: UpdateCurrencyDto): Promise<CurrencyEntity> {
    return this.currencyService.update(id, updateCurrencyDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.currencyService.remove(id);
  }
}
