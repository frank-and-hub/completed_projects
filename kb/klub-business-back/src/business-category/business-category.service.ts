import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateBusinessCategoryDto, UpdateBusinessCategoryDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';


@Injectable()
export class BusinessCategoryService {
  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, description: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createBusinessCategoryDto: CreateBusinessCategoryDto) {
    const newBusinessCategory = await this.db.businessCategory.create({ data: { ...createBusinessCategoryDto } });
    return await this.findOne(newBusinessCategory.id);
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

    const businessCategorys = await this.db.businessCategory.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.businessCategory.count({ where: whereClause });

    return {
      data: businessCategorys,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Business Category List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.businessCategory.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Business category not found with the given ID');
    if (data.deletedAt) throw new GoneException('Business category has been deleted');
    // if (!data.status) throw new ForbiddenException('Business category is inactive');
    return data;
  }

  async update(id: string, updateBusinessCategoryDto: UpdateBusinessCategoryDto) {
    await this.db.businessCategory.update({ where: { id }, data: { ...updateBusinessCategoryDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.businessCategory.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
