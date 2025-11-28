import {
  Controller,
  Get,
  Post,
  Body,
  Patch,
  Param,
  Delete,
  UseGuards,
} from '@nestjs/common';
import { PermissionService } from './permission.service';
import { CreatePermissionDto } from './dto/create-permission.dto';
import { UpdatePermissionDto } from './dto/update-permission.dto';
import { JwtAuthGuard, RoleBasedGuard } from 'src/auth/guards';
import { Roles } from 'src/auth/decorator/roles.decorator';

@Controller({ path: 'permission', version: '1' })
@UseGuards(JwtAuthGuard)
export class PermissionController {
  constructor(private readonly permissionService: PermissionService) {}

  @Post()
  @UseGuards(RoleBasedGuard)
  @Roles('ADMIN', 'SUPER_ADMIN')
  create(@Body() createPermissionDto: CreatePermissionDto) {
    return this.permissionService.create(createPermissionDto);
  }

  @Get()
  findAll() {
    return this.permissionService.findAll();
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.permissionService.findOne(id);
  }

  @Patch(':id')
  @UseGuards(RoleBasedGuard)
  @Roles('ADMIN', 'SUPER_ADMIN')
  update(@Param('id') id: string, @Body() updatePermissionDto: UpdatePermissionDto) {
    return this.permissionService.update(id, updatePermissionDto);
  }

  @Delete(':id')
  @UseGuards(RoleBasedGuard)
  @Roles('ADMIN', 'SUPER_ADMIN')
  remove(@Param('id') id: string) {
    return this.permissionService.remove(id);
  }

  @Post('role/:roleId/assign')
  @UseGuards(RoleBasedGuard)
  @Roles('ADMIN', 'SUPER_ADMIN')
  assignPermissionToRole(@Param('roleId') roleId: string, @Body() body: { permissionId: string }) {
    return this.permissionService.assignPermissionToRole(roleId, body.permissionId);
  }

  @Delete('role/:roleId/remove')
  @UseGuards(RoleBasedGuard)
  @Roles('ADMIN', 'SUPER_ADMIN')
  removePermissionFromRole(@Param('roleId') roleId: string, @Body() body: { permissionId: string }) {
    return this.permissionService.removePermissionFromRole(roleId, body.permissionId);
  }

  @Get('role/:roleId')
  getRolePermissions(@Param('roleId') roleId: string) {
    return this.permissionService.getRolePermissions(roleId);
  }
}
