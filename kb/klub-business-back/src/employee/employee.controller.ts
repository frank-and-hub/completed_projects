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
import { EmployeeService } from './employee.service';
import { CreateEmployeeDto } from './dto/create-employee.dto';
import { UpdateEmployeeDto } from './dto/update-employee.dto';
import { JwtAuthGuard, BusinessOwnerGuard } from 'src/auth/guards';

@Controller({ path: 'employee', version: '1' })
@UseGuards(JwtAuthGuard)
export class EmployeeController {
  constructor(private readonly employeeService: EmployeeService) {}

  @Post()
  @UseGuards(BusinessOwnerGuard)
  create(@Body() createEmployeeDto: CreateEmployeeDto) {
    return this.employeeService.create(createEmployeeDto);
  }

  @Get()
  findAll(
    @Query('userId') userId?: string,
    @Query('departmentId') departmentId?: string,
  ) {
    if (userId) {
      return this.employeeService.findByUserId(userId);
    }
    if (departmentId) {
      return this.employeeService.findByDepartment(departmentId);
    }
    return this.employeeService.findAll();
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.employeeService.findOne(id);
  }

  @Get(':id/attendance')
  getAttendance(
    @Param('id') id: string,
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.employeeService.getAttendance(
      id,
      startDate ? new Date(startDate) : undefined,
      endDate ? new Date(endDate) : undefined,
    );
  }

  @Get(':id/salary')
  getSalaryRecords(@Param('id') id: string) {
    return this.employeeService.getSalaryRecords(id);
  }

  @Get(':id/performance')
  getPerformanceLogs(@Param('id') id: string) {
    return this.employeeService.getPerformanceLogs(id);
  }

  @Patch(':id')
  @UseGuards(BusinessOwnerGuard)
  update(@Param('id') id: string, @Body() updateEmployeeDto: UpdateEmployeeDto) {
    return this.employeeService.update(id, updateEmployeeDto);
  }

  @Delete(':id')
  @UseGuards(BusinessOwnerGuard)
  remove(@Param('id') id: string) {
    return this.employeeService.remove(id);
  }
}
