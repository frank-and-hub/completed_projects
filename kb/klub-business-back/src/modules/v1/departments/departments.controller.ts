import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query, ParseUUIDPipe } from '@nestjs/common';
import { DepartmentsService } from './departments.service';
import { CreateDepartmentDto, UpdateDepartmentDto, QueryDepartmentDto } from './dto';
import { JwtGuard } from 'src/auth/guards';
import { DepartmentEntity } from '../entities';


@UseGuards(JwtGuard)
@Controller({ path: 'departments', version: '1' })
export class DepartmentsController {
  constructor(private readonly departmentsService: DepartmentsService) { }

  @Get()
  index(@Query() query: QueryDepartmentDto) {
    return this.departmentsService.findAll(query);
  }

  @Post()
  create(@Body() createDepartmentDto: CreateDepartmentDto): Promise<DepartmentEntity> {
    return this.departmentsService.create(createDepartmentDto);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<DepartmentEntity> {
    return this.departmentsService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateDepartmentDto: UpdateDepartmentDto): Promise<DepartmentEntity> {
    return this.departmentsService.update(id, updateDepartmentDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.departmentsService.remove(id);
  }
}
