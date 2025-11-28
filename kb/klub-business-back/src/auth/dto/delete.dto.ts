import { Field, InputType } from "@nestjs/graphql";
import { IsDateString, IsOptional } from "class-validator";

@InputType()
export class DeleteDto {
    
    @Field(() => String)
    @IsOptional()
    @IsDateString()
    deletedAt: string;
}