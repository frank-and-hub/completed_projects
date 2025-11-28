import { IsDateString, IsEnum, IsNumber, IsOptional, IsString, ValidateIf } from "class-validator";
import { Type } from "class-transformer";
import { Field, Float, InputType } from "@nestjs/graphql";
import { CurrencyEntity } from "src/currency/entities";
import { DurationType, PlanType, Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreatePlanDto {

  @Field(() => String)
  @IsString()
  name: string;

  @Field(() => CurrencyEntity)
  @IsOptional()
  currencyId: string;

  @Field(() => String)
  @IsString()
  description: string;

  // @IsOptional()
  // @Type(() => Number) // ensures string input like "12.5" becomes a number
  // @IsNumber()
  // amount?: number;

  @Field(() => Float)
  @ValidateIf((o) => o.amount !== undefined)
  @Type(() => Number)
  @IsNumber({ maxDecimalPlaces: 2 }, { message: 'Amount must be a decimal with at most 2 decimal places' })
  amount?: number;

  @Field(() => Date, PlanType)
  @IsOptional()
  @IsEnum(PlanType)
  planType: typeof PlanType;

  @Field(() => Number)
  @IsNumber()
  duration: number;

  @Field(() => DurationType)
  @IsOptional()
  @IsEnum(DurationType)
  durationType: typeof DurationType;

  @Field(() => Status)
  @IsOptional()
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
