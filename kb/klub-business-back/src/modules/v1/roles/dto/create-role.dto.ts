import { Field, InputType } from "@nestjs/graphql";
import { Status } from "@prisma/client";
import { IsDateString, IsEnum, IsOptional, IsString } from "class-validator";

@InputType()
export class CreateRoleDto {

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
