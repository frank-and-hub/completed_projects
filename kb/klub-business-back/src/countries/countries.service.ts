import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateCountryDto, UpdateCountryDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class CountriesService {
  constructor(private readonly db: DbService) { }
  private readonly selectedColumns = { id: true, name: true, iso2: true, iso3: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createCountryDto: CreateCountryDto) {
    const newCountry = await this.db.country.create({ data: { ...createCountryDto } });
    return await this.findOne(newCountry.id);
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
          { iso2: { contains: search, mode: 'insensitive' } },
          { iso3: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const countrys = await this.db.country.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.country.count({ where: whereClause });

    return {
      data: countrys,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Countries List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.country.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Country not found with the given ID');
    if (data.deletedAt) throw new GoneException('Country has been deleted');
    // if (!data.status) throw new ForbiddenException('Country is inactive');
    return data;
  }

  async update(id: string, updateCountryDto: UpdateCountryDto) {
    await this.db.country.update({ where: { id }, data: { ...updateCountryDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.country.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
