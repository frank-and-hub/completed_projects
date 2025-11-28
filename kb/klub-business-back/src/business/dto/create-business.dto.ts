import { IsDateString, IsEnum, IsOptional, IsString, IsUUID } from "class-validator";
import { Field, InputType } from "@nestjs/graphql";
import { BusinessCategoryEntity } from "src/business-category/entities";
import { CityEntity } from "src/cities/entities";
import { StateEntity } from "src/states/entities";
import { CountryEntity } from "src/countries/entities";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateBusinessDto {

    @Field(() => String)
    @IsString()
    name: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    description?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    phone?: string;

    @Field(() => Boolean, { defaultValue: false })
    @IsString()
    isVerified: boolean;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    latitude?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    longitude?: string;

    @Field(() => BusinessCategoryEntity)
    @IsString()
    @IsUUID()
    businessCategoryId: string;

    @Field(() => CityEntity, { nullable: true })
    @IsOptional()
    @IsString()
    @IsUUID()
    cityId?: string;

    @Field(() => StateEntity, { nullable: true })
    @IsOptional()
    @IsString()
    @IsUUID()
    stateId?: string;

    @Field(() => CountryEntity, { nullable: true })
    @IsOptional()
    @IsString()
    @IsUUID()
    countryId?: string;

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
