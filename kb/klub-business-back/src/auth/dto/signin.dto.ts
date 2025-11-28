import { Field, InputType } from "@nestjs/graphql";
import { IsNotEmpty, IsString, IsEnum, IsEmpty, IsOptional, IsBoolean } from "class-validator";
// import { Transform } from 'class-transformer';
// import { DeviceType } from '../../users/dto/index';

@InputType()
export class SigninDto {

    @Field(() => String)
    @IsNotEmpty()
    @IsString()
    email: string;

    @Field(() => String)
    @IsNotEmpty()
    @IsString()
    password: string;

    @Field(() => Boolean, { defaultValue: false })
    @IsOptional()
    @IsBoolean()
    rememberMe?: boolean;

}