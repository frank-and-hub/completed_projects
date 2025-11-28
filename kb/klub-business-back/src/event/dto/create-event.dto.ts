import { Field, InputType } from '@nestjs/graphql';
import { IsString, IsBoolean, IsDateString, IsOptional, IsUrl } from 'class-validator';
import { BusinessEntity } from 'src/business/entities';

@InputType()
export class CreateEventDto {

  @Field(() => BusinessEntity)
  @IsString()
  businessId: string;

  @Field(() => String)
  @IsString()
  title: string;

  @Field(() => String, { nullable: true })
  @IsOptional()
  @IsString()
  description?: string;

  @Field(() => Date)
  @IsDateString()
  startDate: Date;

  @Field(() => Date)
  @IsOptional()
  @IsDateString()
  endDate?: string;

  @Field(() => String)
  @IsOptional()
  @IsUrl()
  url?: string;

  @Field(() => Boolean, { defaultValue: true })
  @IsOptional()
  @IsBoolean()
  inPublic?: boolean;

  
}
