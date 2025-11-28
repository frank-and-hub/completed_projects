import { EmployeeStatus, EmployeeShiftType, Status } from 'src/common/enums/prisma-enums';
import { Field, ObjectType } from '@nestjs/graphql';
import { DepartmentEntity } from 'src/modules/v1/entities/department.entity';

@ObjectType()
export class Employee {
  @Field(() => String)
  id: string;

  @Field(() => String)
  userId: string;

  @Field(() => Date)
  hireDate: Date;

  @Field(() => Number)
  baseSalary: number;

  @Field(() => DepartmentEntity, { nullable: true })
  department?: DepartmentEntity | null;

  @Field(() => EmployeeStatus)
  employeeStatus: typeof EmployeeStatus;

  @Field(() => EmployeeShiftType)
  shiftType: typeof EmployeeShiftType;

  @Field(() => Status)
  status: typeof Status;

  @Field(() => Date)
  createdAt: Date;

  @Field(() => Date)
  updatedAt: Date;
  deletedAt?: Date | null;
}
