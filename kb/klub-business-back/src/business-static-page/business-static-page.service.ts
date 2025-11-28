import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateBusinessStaticPageDto, UpdateBusinessStaticPageDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class BusinessStaticPageService {
  constructor(private readonly db: DbService) { }
  private readonly selectedColumns = { id: true, name: true, description: true, business: { select: { id: true, name: true } }, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createBusinessStaticPageDto: CreateBusinessStaticPageDto) {
    const newCurrency = await this.db.businessStaticPage.create({ data: { ...createBusinessStaticPageDto } });
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

    const currencies = await this.db.businessStaticPage.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.businessStaticPage.count({ where: whereClause });

    return {
      data: currencies,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Business Pages List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.businessStaticPage.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Business Static Page not found with the given ID');
    if (data.deletedAt) throw new GoneException('Business Static Page has been deleted');
    // if (!data.status) throw new ForbiddenException('Business Static Page is inactive');
    return data;
  }

  async update(id: string, updateBusinessStaticPageDto: UpdateBusinessStaticPageDto) {
    await this.db.businessStaticPage.update({ where: { id }, data: { ...updateBusinessStaticPageDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.businessStaticPage.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
