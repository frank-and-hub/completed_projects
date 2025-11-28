import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateMenuDto, UpdateMenuDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto'
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class MenuService {
  constructor(private readonly db: DbService) { }
  
  private readonly selectedColumns = { id: true, name: true, slug: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createMenuDto: CreateMenuDto) {
    const newMenu = await this.db.menu.create({ data: { ...createMenuDto } });
    return await this.findOne(newMenu.id);
  }

  async findAll(query: QueryDto) {
    const { search = '', page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;

    const whereClause: any = {
      deletedAt: null,
      ...(status && { status }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { slug: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const menus = await this.db.menu.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.menu.count({ where: whereClause });

    return {
      data: menus,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Menu List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.menu.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Menu not found with the given ID');
    if (data.deletedAt) throw new GoneException('Menu has been deleted');
    // if (!data.status) throw new ForbiddenException('Menu is inactive');
  }

  async update(id: string, updateMenuDto: UpdateMenuDto) {
    await this.db.menu.update({ where: { id }, data: { ...updateMenuDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.menu.update({ where: { id }, data: { deletedAt: new Date() } });
  }

  async removeAll() {
    this.db.menu.deleteMany();
    return null;
  }
}
