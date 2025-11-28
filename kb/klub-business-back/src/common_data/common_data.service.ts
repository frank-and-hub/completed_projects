import { Inject, Injectable } from '@nestjs/common';
import { QueryDto } from '../common_data/dto/query.dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';
import { Cache } from '@nestjs/cache-manager';

@Injectable()
export class CommonDataService {
  constructor(@Inject('CACHE_MANAGER') private cacheManager: Cache, private readonly db: DbService) { }

  private readonly selectedColumns = { id: true, name: true, status: true };

  // private createInsensitiveSearch(fieldPath: string, query: QueryDto): any {
  //   const fields = fieldPath.split('.');
  //   const searchCondition = { contains: query.search, mode: 'insensitive' };
  //   return fields.reduceRight((acc, field) => ({ [field]: acc }), searchCondition);
  // }

  private createInsensitiveSearch(name: string, query: QueryDto) {
    return { [name]: { contains: query.search, mode: 'insensitive' } };
  };

  private async getRoleData(query: QueryDto) {
    return await this.getCommonDataQuery(
      query,
      this.db.role,
      this.selectedColumns,
      [this.createInsensitiveSearch(`name`, query)],
    );
  }

  private async getCityData(query: QueryDto) {
    return await this.getCommonDataQuery(
      query,
      this.db.city,
      {
        ...this.selectedColumns,
        // state: { select: this.selectedColumns },
        // country: { select: this.selectedColumns },
      },
      [
        this.createInsensitiveSearch(`name`, query),
        // this.createInsensitiveSearch('state.name', query),
        // this.createInsensitiveSearch('country.name', query),
      ],
    );
  }

  private async getStateData(query: QueryDto) {
    return await this.getCommonDataQuery(
      query,
      this.db.state,
      // country: { select: this.selectedColumns },
      {
        ...this.selectedColumns,
        // country: { select: this.selectedColumns },
      },
      [
        this.createInsensitiveSearch(`name`, query),
        // this.createInsensitiveSearch('country.name', query),
      ],
    );
  }

  private async getCountryData(query: QueryDto) {
    return await this.getCommonDataQuery(
      query,
      this.db.country,
      this.selectedColumns,
      [this.createInsensitiveSearch(`name`, query)],
    );
  }

  private async getCurrencyData(query: QueryDto) {
    return await this.getCommonDataQuery(
      query,
      this.db.currency,
      this.selectedColumns,
      [this.createInsensitiveSearch(`name`, query)],
    );
  }

  private async getCountryDialCodeData(query: QueryDto) {
    const selected = { id: true, code: true, name: true, status: true };
    const result = await this.getCommonDataQuery(
      query,
      this.db.country,
      selected,
      [this.createInsensitiveSearch(`code`, query)],
    );
    let data = result.data.map((item) => ({
      id: item.code,
      name: `+ ${item.code} ${item.name}`,
      status: item.status,
    }));

    return {
      data,
      pagination: result.pagination,
      title: result.title,
    };
  }

  async findOne(query: QueryDto) {
    const { type } = query;
    console.log(`object;`, type)
    const handlers: Record<string, (query: QueryDto) => Promise<any>> = {
      role: this.getRoleData.bind(this),
      state: this.getStateData.bind(this),
      city: this.getCityData.bind(this),
      currency: this.getCurrencyData.bind(this),
      country: this.getCountryData.bind(this),
      dialCode: this.getCountryDialCodeData.bind(this),
    };
    const handler = handlers[type];
    if (!handler) throw new Error(`Invalid type: ${type}`);
    return handler(query);
  }

  private async getCommonDataQuery(
    query: QueryDto,
    dataTable: any,
    selectedColumns: any,
    searcableAItem: any[] = [],
  ) {

    const { type, search, page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc' } = query;

    const take = parseInt(limit);
    const skip = (parseInt(page) - 1) * take;

    const whereClause: any = {
      deletedAt: null,
      ...(status && { status }),
      ...(search && { OR: searcableAItem }),
    };

    const data = await dataTable.findMany({ where: whereClause, orderBy: { [orderBy]: direction }, skip, take, select: selectedColumns });
    const total = await dataTable.count({ where: whereClause });

    return {
      data,
      pagination: {
        total,
        page: parseInt(page),
        limit: take,
        pages: Math.ceil(total / take),
      },
      title: type
    };
  }
}
