import { Injectable, Query, NotFoundException, GoneException, ForbiddenException } from '@nestjs/common';
import { CreateStateDto, UpdateStateDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class StatesService {

  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, country: { select: { id: true, name: true } }, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createStateDto: CreateStateDto) {
    const newState = await this.db.state.create({ data: { ...createStateDto } });
    return await this.findOne(newState.id);
  }

  async findAll(@Query() query: QueryDto) {
    const { search, page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;
    

    const whereClause: any = {
      deletedAt: null,
      ...(status && { status }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { country: { name: { contains: search, mode: 'insensitive' } } },
        ],
      }),
    };

    const states = await this.db.state.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.state.count({ where: whereClause });

    return {
      data: states,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'States List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.state.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('State not found with the given ID');
    if (data.deletedAt) throw new GoneException('State has been deleted');
    // if (!data.status) throw new ForbiddenException('State is inactive');
    return data;
  }

  async update(id: string, updateStateDto: UpdateStateDto) {
    await this.db.state.update({ where: { id }, data: { ...updateStateDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.state.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
