import { Injectable, NotFoundException } from '@nestjs/common';
import { DbService } from 'src/database/db.service';
import { CreateEmployeeDto,UpdateEmployeeDto } from './dto';
import { Employee } from './entities/employee.entity';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class EmployeeService {
  constructor(private readonly db: DbService) { }

  async create(createEmployeeDto: CreateEmployeeDto): Promise<Employee> {
    return this.db.employee.create({
      data: { ...createEmployeeDto },
      include: { user: true, department: {include: {businessCategory: true}} },
    });
  }

  async findAll(): Promise<Employee[]> {
    return this.db.employee.findMany({
      where: { status: Status.ACTIVE },
      include: { user: true, department: {include: {businessCategory: true}} },
    });
  }

  async findOne(id: string): Promise<Employee> {
    const employee = await this.db.employee.findUnique({
      where: { id },
      include: { user: true, department: {include: {businessCategory: true}} },
    });

    if (!employee) {
      throw new NotFoundException(`Employee with ID ${id} not found`);
    }

    return employee;
  }

  async findByUserId(userId: string): Promise<Employee[]> {
    return this.db.employee.findMany({
      where: { userId, status: Status.ACTIVE },
      include: { user: true, department: {include: {businessCategory: true}} },
    });
  }

  async findByDepartment(departmentId: string): Promise<Employee[]> {
    return this.db.employee.findMany({
      where: { departmentId, status: Status.ACTIVE },
      include: { user: true, department: {include: {businessCategory: true}} },
    });
  }

  async update(id: string, updateEmployeeDto: UpdateEmployeeDto): Promise<Employee> {
    await this.findOne(id); // Check if exists

    return this.db.employee.update({
      where: { id },
      data: updateEmployeeDto,
      include: { user: true, department: {include: {businessCategory: true}} },
    });
  }

  async remove(id: string): Promise<Employee> {
    await this.findOne(id); // Check if exists

    return this.db.employee.update({
      where: { id },
      data: { status: 'INACTIVE' },
    });
  }

  async getAttendance(employeeId: string, startDate?: Date, endDate?: Date) {
    return this.db.attendance.findMany({
      where: {
        employeeId,
        ...(startDate && endDate && {
          date: {
            gte: startDate,
            lte: endDate,
          },
        }),
      },
      orderBy: { date: 'desc' },
    });
  }

  async getSalaryRecords(employeeId: string) {
    return this.db.salary.findMany({
      where: { employeeId },
      orderBy: { createdAt: 'desc' },
    });
  }

  async getPerformanceLogs(employeeId: string) {
    return this.db.performance.findMany({
      where: { employeeId },
      orderBy: { date: 'desc' },
    });
  }
}
