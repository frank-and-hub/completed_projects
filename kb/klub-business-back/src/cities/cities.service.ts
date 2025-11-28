import { GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateCityDto, UpdateCityDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class CitiesService {
  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, state: { select: { id: true, name: true } }, country: { select: { id: true, name: true } }, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createCityDto: CreateCityDto) {
    const newCity = await this.db.city.create({ data: { ...createCityDto } });
    return await this.findOne(newCity.id);
  }

  async findAll(query: QueryDto) {
    // return `This action returns all cities`;
    const { search, page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;
    

    const whereClause: any = {
      deletedAt: null,
      ...(status && { status }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { state: { name: { contains: search, mode: 'insensitive' } } },
          { country: { name: { contains: search, mode: 'insensitive' } } },
        ],
      }),
    };

    const citys = await this.db.city.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.city.count({ where: whereClause });

    return {
      data: citys,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Cities List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.city.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('City not found with the given ID');
    if (data.deletedAt) throw new GoneException('City has been deleted');
    // if (!data.status) throw new ForbiddenException('City is inactive');
    return data;
  }

  async update(id: string, updateCityDto: UpdateCityDto) {
    await this.db.city.update({ where: { id }, data: { ...updateCityDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.city.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
