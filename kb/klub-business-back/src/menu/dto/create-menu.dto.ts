import { Field, InputType } from "@nestjs/graphql";
import { IsOptional, IsString, IsDateString, IsEmpty, IsEnum } from "class-validator";
import { MenuEntity } from "../entities";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateMenuDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String)
    @IsString()
    slug: string;

    @Field(() => String)
    @IsEmpty()
    @IsString()
    route?: string;

    @Field(() => String, { nullable: true })
    @IsEmpty()
    @IsString()
    icon?: string;

    @Field(() => MenuEntity, { nullable: true })
    @IsOptional()
    parentId: string;

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
