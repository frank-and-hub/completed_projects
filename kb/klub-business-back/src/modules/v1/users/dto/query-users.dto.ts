import { Field, ObjectType } from '@nestjs/graphql';
import { IsOptional, IsString, IsEnum, IsNumberString } from 'class-validator';
import { DeviceType, Gender, RelationshipStatus, Status, SortDirection } from 'src/common/enums/prisma-enums';

@ObjectType()
export class QueryUsersDto {

    @Field(() => String)
    @IsOptional()
    @IsString()
    role?: string;

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
    @IsString()
    status?: boolean | string;

    @Field(() => SortDirection, { defaultValue: SortDirection.asc })
    @IsOptional()
    @IsEnum(SortDirection)
    direction?: SortDirection;

    @Field(() => Gender)
    @IsOptional()
    @IsEnum(Gender)
    gender?: string;

    @Field(() => DeviceType)
    @IsOptional()
    @IsEnum(DeviceType)
    deviceType?: string;

    @Field(() => RelationshipStatus)
    @IsOptional()
    @IsEnum(RelationshipStatus)
    relationshipStatus?: string;
}
