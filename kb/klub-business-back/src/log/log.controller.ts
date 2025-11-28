import { Controller, Get, Post, Body, Patch, Param, Delete, Query, ParseUUIDPipe } from '@nestjs/common';
import { LogService } from './log.service';
import { CreateLogDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { LogEntity } from './entities';

@Controller({ path: 'log', version: '1' })
export class LogController {
  constructor(private readonly logService: LogService) { }

  @Post()
  create(@Body() createLogDto: CreateLogDto): Promise<LogEntity> {
    return this.logService.create(createLogDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.logService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<LogEntity> {
    return this.logService.findOne(id);
  }
}
