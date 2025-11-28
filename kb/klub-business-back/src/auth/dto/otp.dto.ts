import { Field, InputType } from "@nestjs/graphql";
import { IsNotEmpty, IsNumber } from "class-validator";


@InputType()
export class OtpDto {

    @Field(() => String)
    @IsNumber()
    @IsNotEmpty()
    otp: string;

}