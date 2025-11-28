import { Injectable, NotFoundException } from '@nestjs/common';
import { CreateLogDto } from './dto';
import { DbService } from 'src/database';
import { QueryDto } from 'src/auth/dto'
import { app } from 'src/auth/constants';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class LogService {
  constructor(private readonly db: DbService) { }

  protected readonly selectedColumns = { id: true, model: true, action: true, query: true, durationMs: true, createdAt: true };

  async create(createLogDto: CreateLogDto) {
    const newLog = await this.db.log.create({ data: { ...createLogDto } });
    return await this.findOne(newLog.id);
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
          { model: { contains: search, mode: 'insensitive' } },
          { query: { contains: search, mode: 'insensitive' } },
        ],
      }),
    };

    const logs = await this.db.log.findMany({
      where: whereClause,
      orderBy: { [orderBy]: direction },
      skip,
      take,
      select: this.selectedColumns,
    });

    const total = await this.db.log.count({ where: whereClause });

    return {
      data: logs,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
         title:'Logs List'
    };
  }


  async findOne(id: string) {
    const data = await this.db.log.findUnique({ where: { id }, select: this.selectedColumns });
    if (!data) throw new NotFoundException('Log not found with the given ID');
    return data;
  }
}
