import { Field, ObjectType } from '@nestjs/graphql';
import { IsOptional, IsString, IsEnum, IsNumberString } from 'class-validator';
import { BusinessCategoryEntity } from 'src/business-category/entities';
import { SortDirection } from 'src/common/enums/prisma-enums';

@ObjectType()
export class QueryDepartmentDto {

    @Field(() => BusinessCategoryEntity)
    @IsOptional()
    businessCategory?: string;

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

    @Field(() => SortDirection, { nullable: true })
    @IsOptional()
    @IsEnum(SortDirection)
    direction?: typeof SortDirection;
}
