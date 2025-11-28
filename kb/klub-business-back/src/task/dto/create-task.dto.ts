import { IsString, IsEnum, IsBoolean, IsDateString, IsOptional } from 'class-validator';
import { DurationType, PriorityType } from 'src/common/enums/prisma-enums';
import { Field, InputType } from '@nestjs/graphql';

@InputType()
export class CreateTaskDto {

  @Field(() => String)
  @IsString()
  businessId: string;

  @Field(() => String)
  @IsString()
  assignedById: string;

  @Field(() => String, { nullable: true })
  @IsOptional()
  @IsString()
  assignedToId?: string;

  @Field(() => String)
  @IsString()
  title: string;

  @Field(() => String)
  @IsString()
  description: string;

  @Field(() => DurationType)
  @IsOptional()
  @IsEnum(DurationType)
  durationType?: typeof DurationType;

  @IsEnum(PriorityType)
  priority: typeof PriorityType;

  @Field(() => String)
  @IsDateString()
  deadline: string;

  @Field(() => Boolean, { defaultValue: false })
  @IsOptional()
  @IsBoolean()
  isCompleted?: boolean;
}
