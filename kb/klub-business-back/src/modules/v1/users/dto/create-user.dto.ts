import { IsBoolean, IsDate, IsEmail, IsEnum, IsNotEmpty, IsOptional, IsString, IsUUID } from "class-validator";
import { Type } from 'class-transformer';
import { Field, InputType } from '@nestjs/graphql';
import { DeviceType, Gender, RelationshipStatus, Status } from "@prisma/client";

@InputType()
export class CreateUserDto {

    @Field(() => String)
    @IsNotEmpty({ message: 'First name is required' })
    @IsString()
    firstName: string;

    @Field(() => String, { nullable: true })
    @IsString()
    @IsOptional()
    middleName?: string;

    @Field(() => String, { nullable: true })
    @IsString()
    @IsOptional()
    lastName?: string;

    @Field(() => String)
    @IsNotEmpty()
    @IsEmail()
    email: string;

    @Field(() => String, { nullable: true })
    @IsString()
    @IsOptional()
    dialCode: string;

    @Field(() => String, { nullable: true })
    @IsString()
    @IsOptional()
    phone: string;

    @Field(() => String, { nullable: true })
    @IsString()
    @IsOptional()
    password?: string;

    @Field(() => Boolean, { defaultValue: true })
    @IsOptional()
    @IsBoolean()
    isNotify?: boolean;

    @Field(() => Boolean, { defaultValue: false })
    @IsOptional()
    @IsBoolean()
    isVerified?: boolean;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsUUID()
    cityId?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsUUID()
    stateId?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsUUID()
    countryId?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsUUID()
    roleId?: string;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsUUID()
    departmentId?: string;

    @Field({ nullable: true })
    @IsOptional()
    @IsEnum(DeviceType)
    deviceType?: DeviceType;

    @Field(() => String, { nullable: true })
    @IsOptional()
    @IsString()
    deviceId?: string;

    @Field({ nullable: true })
    @IsOptional()
    @IsEnum(RelationshipStatus)
    relationshipStatus?: RelationshipStatus;

    @Field({ nullable: true })
    @IsOptional()
    @IsEnum(Gender)
    gender?: Gender;

    @Field({ nullable: true })
    @IsOptional()
    @Type(() => Date)
    @IsDate()
    dateOfBirth?: Date;

    @Field(() => Status, { defaultValue: Status.ACTIVE })
    @IsOptional()
    @IsEnum(Status)
    status?: Status;
}