import { Field, InputType } from "@nestjs/graphql";
import { IsDateString, IsOptional, IsString } from "class-validator";

@InputType()
export class CreateLogDto {

    @Field(() => String)
    @IsOptional()
    @IsString()
    model: string;

    @Field(() => String)
    @IsOptional()
    @IsString()
    action: string;

    @Field(() => String)
    @IsOptional()
    @IsString()
    query: string;

    @Field(() => Number)
    @IsOptional()
    durationMs: number;

    @Field(() => Date)
    @IsDateString()
    createdAt: string;
}
