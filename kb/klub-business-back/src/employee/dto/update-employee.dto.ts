import { PartialType } from '@nestjs/mapped-types';
import { CreateEmployeeDto } from './create-employee.dto';
import { Field, InputType } from '@nestjs/graphql';
import { IsString } from 'class-validator';

@InputType()
export class UpdateEmployeeDto extends PartialType(CreateEmployeeDto) {
    @Field(() => String)
    @IsString()
    id: string;
}
