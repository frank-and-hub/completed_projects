import { IsDateString, IsEnum, IsOptional, IsString } from "class-validator";
import { Field, InputType } from "@nestjs/graphql";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateCountryDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String)
    @IsString()
    iso2: string;

    @Field(() => String)
    @IsString()
    iso3: string;

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
