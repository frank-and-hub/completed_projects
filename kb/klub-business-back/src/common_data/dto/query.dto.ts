import { Field, ObjectType } from '@nestjs/graphql';
import { IsOptional, IsString, IsEnum, IsNumberString, IsNotEmpty } from 'class-validator';
import { SortDirection, Status } from 'src/common/enums/prisma-enums';

@ObjectType()
export class QueryDto {

    @Field(() => String)
    @IsNotEmpty()
    @IsString()
    type: string;

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

    @Field(() => Status)
    @IsOptional()
    @IsEnum(Status)
    status?: typeof Status;

    @Field(() => SortDirection, { defaultValue: SortDirection.asc })
    @IsOptional()
    @IsEnum(SortDirection)
    direction?: SortDirection;
}
