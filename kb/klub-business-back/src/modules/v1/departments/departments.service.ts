import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateDepartmentDto, UpdateDepartmentDto, QueryDepartmentDto } from './dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';


@Injectable()
export class DepartmentsService {
  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, description: true, businessCategory: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createDepartmentDto: CreateDepartmentDto) {
    const newDepartment = await this.db.department.create({ data: { ...createDepartmentDto } });
    return await this.findOne(newDepartment.id);
  }

  async findAll(query: QueryDepartmentDto) {
    const { businessCategory, search, page = app.page, limit = app.limit, orderBy = 'createdAt', direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;

    let businessCategoryId: string | undefined = undefined;

    if (businessCategory) {
      const businessCategoryData = await this.db.businessCategory.findFirst({ where: { name: businessCategory } });
      if (businessCategoryData) businessCategoryId = businessCategoryData.id;
    }

    const whereClause: any = {
      deletedAt: null,
      // ...(status && { status }),
      ...(businessCategoryId && { businessCategoryId }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { description: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const departments = await this.db.department.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.department.count({ where: whereClause });

    return {
      data: departments,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Department List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.department.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Department not found with the given ID');
    if (data.deletedAt) throw new GoneException('Department has been deleted');
    // if (!data.status) throw new ForbiddenException('Department is inactive');
    return data;
  }

  async update(id: string, updateDepartmentDto: UpdateDepartmentDto) {
    await this.db.department.update({ where: { id }, data: { ...updateDepartmentDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.department.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
