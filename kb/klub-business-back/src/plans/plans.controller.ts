import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query, ParseUUIDPipe } from '@nestjs/common';
import { PlansService } from './plans.service';
import { CreatePlanDto, UpdatePlanDto, QueryPlanDto } from './dto';
import { JwtGuard } from 'src/auth/guards';
import { PlanEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'plans', version: '1' })
export class PlansController {
  constructor(private readonly plansService: PlansService) { }

  @Post()
  create(@Body() createPlanDto: CreatePlanDto): Promise<PlanEntity> {
    return this.plansService.create(createPlanDto);
  }

  @Get()
  findAll(@Query() query: QueryPlanDto) {
    return this.plansService.findAll(query);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string): Promise<PlanEntity> {
    return this.plansService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updatePlanDto: UpdatePlanDto): Promise<PlanEntity> {
    return this.plansService.update(id, updatePlanDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.plansService.remove(id);
  }
}
