import { IsDateString, IsOptional, IsString, IsEnum } from "class-validator";
import { Status } from "@prisma/client";
import { Field, InputType } from "@nestjs/graphql";

@InputType()
export class CreateBusinessCategoryDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String)
    @IsString()
    description: string;

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
