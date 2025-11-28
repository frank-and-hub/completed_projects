import {
  Controller,
  Get,
  Post,
  Body,
  Patch,
  Param,
  Delete,
  UseGuards,
  Query,
} from '@nestjs/common';
import { TaskService } from './task.service';
import { CreateTaskDto } from './dto/create-task.dto';
import { UpdateTaskDto } from './dto/update-task.dto';
import { JwtAuthGuard, BusinessOwnerGuard, EmployeeGuard } from 'src/auth/guards';

@Controller({ path: 'task', version: '1' })
@UseGuards(JwtAuthGuard)
export class TaskController {
  constructor(private readonly taskService: TaskService) {}

  @Post()
  @UseGuards(BusinessOwnerGuard)
  create(@Body() createTaskDto: CreateTaskDto) {
    return this.taskService.create(createTaskDto);
  }

  @Get()
  findAll(
    @Query('assignedTo') assignedTo?: string,
    @Query('assignedBy') assignedBy?: string,
    @Query('businessId') businessId?: string,
    @Query('overdue') overdue?: string,
  ) {
    if (assignedTo) {
      return this.taskService.findByAssignedTo(assignedTo);
    }
    if (assignedBy) {
      return this.taskService.findByAssignedBy(assignedBy);
    }
    if (businessId) {
      return this.taskService.findByBusiness(businessId);
    }
    if (overdue === 'true') {
      return this.taskService.findOverdue();
    }
    return this.taskService.findAll();
  }

  @Get('stats')
  getStats(@Query('userId') userId: string) {
    return this.taskService.getTaskStats(userId);
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.taskService.findOne(id);
  }

  @Patch(':id/complete')
  @UseGuards(EmployeeGuard)
  markAsCompleted(@Param('id') id: string) {
    return this.taskService.markAsCompleted(id);
  }

  @Patch(':id')
  @UseGuards(BusinessOwnerGuard)
  update(@Param('id') id: string, @Body() updateTaskDto: UpdateTaskDto) {
    return this.taskService.update(id, updateTaskDto);
  }

  @Delete(':id')
  @UseGuards(BusinessOwnerGuard)
  remove(@Param('id') id: string) {
    return this.taskService.remove(id);
  }
}
