import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateCurrencyDto, UpdateCurrencyDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class CurrencyService {
  constructor(private readonly db: DbService) { }
  private readonly selectedColumns = { id: true, name: true, shortName: true, symbol: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createCurrencyDto: CreateCurrencyDto) {
    const newCurrency = await this.db.currency.create({ data: { ...createCurrencyDto } });
    return await this.findOne(newCurrency.id);
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
          { shortName: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const currencies = await this.db.currency.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.currency.count({ where: whereClause });

    return {
      data: currencies,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Currency List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.currency.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Currency not found with the given ID');
    if (data.deletedAt) throw new GoneException('Currency has been deleted');
    // if (!data.status) throw new ForbiddenException('Currency is inactive');
    return data;
  }

  async update(id: string, updateCurrencyDto: UpdateCurrencyDto) {
    await this.db.currency.update({ where: { id }, data: { ...updateCurrencyDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.currency.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
