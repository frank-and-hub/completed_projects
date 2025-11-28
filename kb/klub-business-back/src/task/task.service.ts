import { Injectable, NotFoundException } from '@nestjs/common';
import { DbService } from 'src/database/db.service';
import { CreateTaskDto } from './dto/create-task.dto';
import { UpdateTaskDto } from './dto/update-task.dto';
import { Task } from './entities/task.entity';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class TaskService {
  constructor(private readonly db: DbService) {}

  async create(createTaskDto: CreateTaskDto): Promise<Task> {
    return this.db.task.create({
      data: createTaskDto,
      include: { assignedTo: true, assignedBy: true, business: true },
    });
  }

  async findAll(): Promise<Task[]> {
    return this.db.task.findMany({
      where: { status: Status.ACTIVE },
      include: { assignedTo: true, assignedBy: true, business: true },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findOne(id: string): Promise<Task> {
    const task = await this.db.task.findUnique({
      where: { id },
      include: { assignedTo: true, assignedBy: true, business: true },
    });

    if (!task) {
      throw new NotFoundException(`Task with ID ${id} not found`);
    }

    return task;
  }

  async findByAssignedTo(userId: string): Promise<Task[]> {
    return this.db.task.findMany({
      where: { assignedToId: userId, status: Status.ACTIVE },
      include: { assignedTo: true, assignedBy: true, business: true },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findByAssignedBy(userId: string): Promise<Task[]> {
    return this.db.task.findMany({
      where: { assignedById: userId, status: Status.ACTIVE },
      include: { assignedTo: true, assignedBy: true, business: true },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findByBusiness(businessId: string): Promise<Task[]> {
    return this.db.task.findMany({
      where: { businessId, status: Status.ACTIVE },
      include: { assignedTo: true, assignedBy: true, business: true },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findOverdue(): Promise<Task[]> {
    return this.db.task.findMany({
      where: {
        status: Status.ACTIVE,
        isCompleted: false,
        deadline: { lt: new Date() },
      },
      include: { assignedTo: true, assignedBy: true, business: true },
      orderBy: { deadline: 'asc' },
    });
  }

  async markAsCompleted(id: string): Promise<Task> {
    await this.findOne(id); // Check if exists

    return this.db.task.update({
      where: { id },
      data: { isCompleted: true },
      include: { assignedTo: true, assignedBy: true, business: true },
    });
  }

  async update(id: string, updateTaskDto: UpdateTaskDto): Promise<Task> {
    await this.findOne(id); // Check if exists

    return this.db.task.update({
      where: { id },
      data: updateTaskDto,
      include: { assignedTo: true, assignedBy: true, business: true },
    });
  }

  async remove(id: string): Promise<Task> {
    await this.findOne(id); // Check if exists

    return this.db.task.update({
      where: { id },
      data: { status: 'INACTIVE', deletedAt: new Date() },
    });
  }

  async getTaskStats(userId: string) {
    const total = await this.db.task.count({
      where: { assignedToId: userId, status: Status.ACTIVE },
    });

    const completed = await this.db.task.count({
      where: { assignedToId: userId, status: Status.ACTIVE, isCompleted: true },
    });

    const overdue = await this.db.task.count({
      where: {
        assignedToId: userId,
        status: Status.ACTIVE,
        isCompleted: false,
        deadline: { lt: new Date() },
      },
    });

    return {
      total,
      completed,
      pending: total - completed,
      overdue,
      completionRate: total > 0 ? (completed / total) * 100 : 0,
    };
  }
}
