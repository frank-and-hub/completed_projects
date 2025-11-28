import { Field, ObjectType } from "@nestjs/graphql";
import { IsBoolean, IsDateString, IsEnum, IsOptional, IsString, IsUUID } from "class-validator";
import { BusinessEntity } from "src/business/entities";
import { Status } from "src/common/enums/prisma-enums";

@ObjectType()
export class EventEntity {

  @Field(() => String)
  @IsString()
  id: string;

  @Field(() => BusinessEntity)
  @IsString()
  @IsUUID()
  businessId: string;

  @Field(() => String)
  @IsString()
  title: string;


  @Field(() => String)
  @IsString()
  description?: string | null;


  @Field(() => Date)
  @IsString()
  startDate: Date;

  @Field(() => Date, { nullable: true })
  @IsString()
  endDate?: Date | null;

  @Field(() => String, { nullable: true })
  @IsOptional()
  url?: string | null;

  @Field(() => Boolean, { defaultValue: true })
  @IsBoolean()
  inPublic: boolean;

  @Field(() => Status)
  @IsEnum(Status)
  status: typeof Status;

  @Field(() => Date)
  @IsOptional()
  @IsDateString()
  createdAt: Date;

  @Field(() => Date)
  @IsOptional()
  @IsDateString()
  updatedAt: Date;

  @Field(() => Date, { nullable: true })
  @IsOptional()
  @IsDateString()
  deletedAt?: Date | null;
}
