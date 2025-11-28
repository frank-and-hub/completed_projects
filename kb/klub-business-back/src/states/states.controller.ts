import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards, ParseUUIDPipe } from '@nestjs/common';
import { StatesService } from './states.service';
import { CreateStateDto, UpdateStateDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { JwtGuard } from 'src/auth/guards';
import { StateEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'states', version: '1' })
export class StatesController {
  constructor(private readonly statesService: StatesService) { }

  @Post()
  create(@Body() createStateDto: CreateStateDto): Promise<StateEntity> {
    return this.statesService.create(createStateDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.statesService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<StateEntity> {
    return this.statesService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateStateDto: UpdateStateDto): Promise<StateEntity> {
    return this.statesService.update(id, updateStateDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.statesService.remove(id);
  }
}
