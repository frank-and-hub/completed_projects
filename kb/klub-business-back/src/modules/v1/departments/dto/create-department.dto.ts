import { IsDateString, IsOptional, IsString, IsUUID, IsEnum } from "class-validator";
import { Status } from "@prisma/client";
import { Field, InputType } from "@nestjs/graphql";
import { BusinessCategoryEntity } from "src/business-category/entities";

@InputType()
export class CreateDepartmentDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String)
    @IsString()
    description: string;

    @Field(() => BusinessCategoryEntity)
    @IsString()
    @IsUUID()
    businessCategoryId: string;

    @Field(() => Status)
    @IsOptional()
    @IsEnum(Status)
    status: Status;

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
