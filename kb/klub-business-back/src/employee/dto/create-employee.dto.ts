import { IsString, IsEnum, IsNumber, IsDateString, IsOptional } from 'class-validator';
import { EmployeeStatus, EmployeeShiftType } from 'src/common/enums/prisma-enums';
import { Field, InputType } from '@nestjs/graphql';

@InputType()
export class CreateEmployeeDto {
  
  @Field(() => String)
  @IsString()
  userId: string;

  @Field(() => Date)
  @IsDateString()
  hireDate: string;

  @Field(() => Number)
  @IsNumber()
  baseSalary: number;

  @Field(() => String, { nullable: true })
  @IsOptional()
  @IsString()
  departmentId?: string;

  @Field(() => EmployeeStatus, { nullable: true })
  @IsOptional()
  @IsEnum(EmployeeStatus)
  employeeStatus?: typeof EmployeeStatus;

  @Field(() => EmployeeShiftType, { nullable: true })
  @IsOptional()
  @IsEnum(EmployeeShiftType)
  shiftType?: typeof EmployeeShiftType;
}
