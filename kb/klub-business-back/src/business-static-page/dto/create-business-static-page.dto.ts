import { IsDateString, IsEnum, IsOptional, IsString, IsUUID } from "class-validator"
import { Field, InputType } from "@nestjs/graphql";
import { BusinessEntity } from "src/business/entities";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateBusinessStaticPageDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String)
    @IsString()
    description: string;

    @Field(() => BusinessEntity)
    @IsUUID()
    businessId: string;

    @Field(() => String, { nullable: true })
    @IsString()
    slug: string;

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
