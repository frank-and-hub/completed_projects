import { Field, InputType } from '@nestjs/graphql';
import { IsString, IsOptional } from 'class-validator';

@InputType()
export class CreatePermissionDto {

  @Field(() => String)
  @IsString()
  name: string;

  @Field(() => String)
  @IsOptional()
  @IsString()
  description?: string;
}
