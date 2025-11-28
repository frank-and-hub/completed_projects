import { IsOptional, IsString, IsEnum, IsNumberString } from 'class-validator';
import { DurationType, PlanType, SortDirection } from 'src/common/enums/prisma-enums';
import { Field, ObjectType } from '@nestjs/graphql';
import { CurrencyEntity } from 'src/currency/entities';

@ObjectType()
export class QueryPlanDto {

    @Field(() => CurrencyEntity)
    @IsOptional()
    currency?: string;

    @Field(() => PlanType)
    @IsOptional()
    @IsEnum(PlanType)
    planType?: typeof PlanType;

    @Field(() => DurationType)
    @IsOptional()
    @IsEnum(DurationType)
    durationType?: typeof DurationType;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    search?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsNumberString()
    page?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsNumberString()
    limit?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    orderBy?: string;

    @Field(() => SortDirection, { defaultValue: SortDirection.asc })
    @IsOptional()
    @IsEnum(SortDirection)
    direction?: SortDirection;
}
