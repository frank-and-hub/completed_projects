import { GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateBusinessDto, UpdateBusinessDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto';
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class BusinessService {
  constructor(private readonly db: DbService) { }
  private readonly selectedColumns = {
    id: true,
    name: true,
    description: true,
    phone: true,
    isVerified: true,
    businessCategoryId: true,
    businessCategory: { select: { id: true, name: true } },
    owners: { select: { id: true, firstName: true, lastName: true, email: true } },
    employees: { select: { id: true, user: { select: { id: true, firstName: true, lastName: true, email: true } } } },
    status: true,
    createdAt: true,
    updatedAt: true,
    deletedAt: true
  };

  async create(createBusinessDto: CreateBusinessDto) {
    const newData = await this.db.business.create({ data: { ...createBusinessDto } });
    return await this.findOne(newData.id);
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
          { phone: { contains: search, mode: 'insensitive' } },
          { businessCategory: { name: { contains: search, mode: 'insensitive' } } },
        ],
      }),
    };

    const businesses = await this.db.business.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.business.count({ where: whereClause });

    return {
      data: businesses,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Business List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.business.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Business not found with the given ID');
    if (data.deletedAt) throw new GoneException('Business has been deleted');
    // if (!data.status) throw new ForbiddenException('Business is inactive');
    return data;
  }

  async update(id: string, updateBusinessDto: UpdateBusinessDto) {
    await this.db.business.update({ where: { id }, data: { ...updateBusinessDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.business.update({ where: { id }, data: { deletedAt: new Date() } });
  }

  async addOwner(businessId: string, userId: string) {
    return this.db.business.update({
      where: { id: businessId },
      data: {
        owners: {
          connect: { id: userId }
        }
      },
      include: { owners: true }
    });
  }

  async removeOwner(businessId: string, userId: string) {
    return this.db.business.update({
      where: { id: businessId },
      data: {
        owners: {
          disconnect: { id: userId }
        }
      },
      include: { owners: true }
    });
  }

  async getOwners(businessId: string) {
    const business = await this.db.business.findUnique({
      where: { id: businessId },
      select: { owners: { select: { id: true, firstName: true, lastName: true, email: true } } }
    });
    return business?.owners || [];
  }

  async getEmployees(businessId: string) {
    const business = await this.db.business.findUnique({
      where: { id: businessId },
      select: {
        employees: {
          select: {
            id: true,
            user: { select: { id: true, firstName: true, lastName: true, email: true } },
            status: true,
            hireDate: true,
            baseSalary: true
          }
        }
      }
    });
    return business?.employees || [];
  }

  async getUserBusinesses(userId: string) {
    return this.db.business.findMany({
      where: {
        OR: [
          { owners: { some: { id: userId } } },
          { employees: { some: { userId: userId } } }
        ],
        status: Status.ACTIVE,
        deletedAt: null
      },
      select: this.selectedColumns
    });
  }
}
