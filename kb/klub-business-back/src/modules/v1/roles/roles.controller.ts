import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query, ParseUUIDPipe } from '@nestjs/common';
import { RolesService } from './roles.service';
import { CreateRoleDto, UpdateRoleDto } from './dto';
import { JwtGuard } from 'src/auth/guards';
import { QueryDto } from 'src/auth/dto';
import { RoleEntity } from 'src/modules/v1/entities';

@UseGuards(JwtGuard)
@Controller({ path: 'roles', version: '1' })
export class RolesController {
  constructor(private readonly rolesService: RolesService) { }

  @Get()
  async findAll(@Query() query: QueryDto) {
    return this.rolesService.findAll(query);
  }

  @Post()
  async create(@Body() createRoleDto: CreateRoleDto): Promise<RoleEntity> {
    return this.rolesService.create(createRoleDto);
  }

  @Get(':id')
  async findOne(@Param('id', ParseUUIDPipe) id: string): Promise<RoleEntity> {
    return this.rolesService.findOne(id);
  }

  @Patch(':id')
  async update(@Param('id', ParseUUIDPipe) id: string, @Body() updateRoleDto: UpdateRoleDto): Promise<RoleEntity> {
    return this.rolesService.update(id, updateRoleDto);
  }

  @Delete(':id')
  async remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.rolesService.remove(id);
  }
}
