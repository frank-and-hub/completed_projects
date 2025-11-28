import { Injectable, NotFoundException } from '@nestjs/common';
import { DbService } from 'src/database/db.service';
import { CreatePermissionDto } from './dto/create-permission.dto';
import { UpdatePermissionDto } from './dto/update-permission.dto';
import { Permission } from './entities/permission.entity';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class PermissionService {
  constructor(private readonly db: DbService) {}

  async create(createPermissionDto: CreatePermissionDto): Promise<Permission> {
    return this.db.permission.create({
      data: createPermissionDto,
    });
  }

  async findAll(): Promise<Permission[]> {
    return this.db.permission.findMany({
      where: { status: Status.ACTIVE },
    });
  }

  async findOne(id: string): Promise<Permission> {
    const permission = await this.db.permission.findUnique({
      where: { id },
    });

    if (!permission) {
      throw new NotFoundException(`Permission with ID ${id} not found`);
    }

    return permission;
  }

  async update(id: string, updatePermissionDto: UpdatePermissionDto): Promise<Permission> {
    await this.findOne(id); // Check if exists

    return this.db.permission.update({
      where: { id },
      data: updatePermissionDto,
    });
  }

  async remove(id: string): Promise<Permission> {
    await this.findOne(id); // Check if exists

    return this.db.permission.update({
      where: { id },
      data: { status: 'INACTIVE', deletedAt: new Date() },
    });
  }

  async assignPermissionToRole(roleId: string, permissionId: string) {
    return this.db.role.update({
      where: { id: roleId },
      data: {
        permissions: {
          connect: { id: permissionId }
        }
      },
      include: { permissions: true }
    });
  }

  async removePermissionFromRole(roleId: string, permissionId: string) {
    return this.db.role.update({
      where: { id: roleId },
      data: {
        permissions: {
          disconnect: { id: permissionId }
        }
      },
      include: { permissions: true }
    });
  }

  async getRolePermissions(roleId: string) {
    const role = await this.db.role.findUnique({
      where: { id: roleId },
      include: { permissions: true }
    });
    return role?.permissions || [];
  }
}
