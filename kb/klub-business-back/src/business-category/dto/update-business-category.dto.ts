import { PartialType } from '@nestjs/mapped-types';
import { CreateBusinessCategoryDto } from './create-business-category.dto';
import { Field, InputType } from '@nestjs/graphql';
import { IsString } from 'class-validator';

@InputType()
export class UpdateBusinessCategoryDto extends PartialType(CreateBusinessCategoryDto) {
    @Field(() => String)
    @IsString()
    id: string;
}
