import { CreateUserDto } from "./create-user.dto";
import { PartialType } from "@nestjs/mapped-types";
import { Field, InputType } from "@nestjs/graphql";
import { IsString } from "class-validator";

@InputType()
export class UpdateUserDto extends PartialType(CreateUserDto) {
    @Field(() => String)
    @IsString()
    id: string;
 };