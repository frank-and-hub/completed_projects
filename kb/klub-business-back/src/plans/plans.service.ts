import { ForbiddenException, GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreatePlanDto, UpdatePlanDto, QueryPlanDto } from './dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';

@Injectable()
export class PlansService {
  constructor(private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, description: true, amount: true, currency: true, planType: true, duration: true, durationType: true, status: true, createdAt: true, updatedAt: true, deletedAt: true };

  async create(createPlanDto: CreatePlanDto) {
    const newPlan = await this.db.plan.create({ data: { ...createPlanDto } });
    return await this.findOne(newPlan.id);
  }

  async findAll(query: QueryPlanDto) {
    const { currency, planType, durationType, search, page = app.page, limit = app.limit, orderBy = 'createdAt', direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;

    let currencyId: string | undefined = undefined;

    if (currency) {
      const currencyData = await this.db.currency.findFirst({ where: { name: currency } });
      if (currencyData) currencyId = currencyData.id;
    }

    const whereClause: any = {
      deletedAt: null,
      // ...(status && { status }),
      ...(currencyId && { currencyId }),
      ...(planType && { planType }),
      ...(durationType && { durationType }),
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' } },
          { description: { contains: search, mode: 'insensitive' } },
          { currency: { name: { contains: search, mode: 'insensitive' } } },
        ],
      }),
    };

    const plans = await this.db.plan.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.plan.count({ where: whereClause });

    return {
      data: plans,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: 'Plans List'
    };
  }

  async findOne(id: string) {
    const data = await this.db.plan.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Plan not found with the given ID');
    if (data.deletedAt) throw new GoneException('Plan has been deleted');
    // if (!data.status) throw new ForbiddenException('Plan is inactive');
    return data;
  }

  async update(id: string, updatePlanDto: UpdatePlanDto) {
    await this.db.plan.update({ where: { id }, data: { ...updatePlanDto } });
    return await this.findOne(id);
  }

  async remove(id: string) {
    return this.db.plan.update({ where: { id }, data: { deletedAt: new Date() } });
  }
}
