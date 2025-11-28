import { Field, InputType } from "@nestjs/graphql";
import { IsEmail, IsNotEmpty, IsString, IsOptional, IsBoolean, MinLength, MaxLength } from "class-validator";
// import { Transform } from 'class-transformer';
// import { DeviceType } from '../../users/dto/index';

@InputType()
export class SignupDto {

    @Field(() => String)
    @IsString()
    @IsNotEmpty()
    name: string;

    @Field(() => String)
    @IsString()
    @IsNotEmpty()
    dialCode: string;

    @Field(() => String)
    @IsString()
    @MinLength(6)
    @MaxLength(16)
    @IsNotEmpty()
    phone: string;

    @Field(() => String)
    @IsString()
    @IsNotEmpty()
    @IsEmail()
    email: string;

    @Field(() => String)
    @IsString()
    @IsNotEmpty()
    password: string;

    @Field(() => String)
    @IsString()
    @IsNotEmpty()
    confirmPassword: string;

    @Field(() => Boolean, { defaultValue: true })
    @IsOptional()
    @IsBoolean()
    aggreTerm: boolean;
}