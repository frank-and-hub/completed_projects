import { GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateRoleDto, UpdateRoleDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';

@Injectable()
export class RolesService {
  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, description: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createRoleDto: CreateRoleDto) {
    console.log({...createRoleDto});
    const newRole = await this.db.role.create({ data: { ...createRoleDto } });
    return await this.findOne(newRole.id);
  }

  async findAll(query: QueryDto) {
    const { search, page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;
    
    const whereClause: any = {
      deletedAt: null,
      ...(status && { status }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { description: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const roles = await this.db.role.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.role.count({ where: whereClause });

    return {
      data: roles,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Role List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.role.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Role not found with the given ID');
    if (data.deletedAt) throw new GoneException('Role has been deleted');
    // if (!data.status) throw new ForbiddenException('Role is inactive');
    return data;
  }

  async update(id: string, updateRoleDto: UpdateRoleDto) {
    await this.db.role.update({ where: { id }, data: { ...updateRoleDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.role.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
