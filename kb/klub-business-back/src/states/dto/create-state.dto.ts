import { IsDateString, IsEnum, IsOptional, IsString, IsUUID } from "class-validator";
import { Field, InputType } from "@nestjs/graphql";
import { CountryEntity } from "src/countries/entities";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateStateDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => CountryEntity)
    @IsUUID()
    @IsString()
    countryId: string;

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
