import { Field, InputType } from "@nestjs/graphql";
import { IsDateString, IsEnum, IsOptional, IsString } from "class-validator";
import { FileType, Status } from "src/common/enums/prisma-enums";

@InputType()
export class CreateFileDto {

  @Field(() => String)
  @IsString()
  name: string;

  @Field(() => String)
  @IsString()
  path: string;

  @Field(() => String)
  @IsString()
  url: string;

  @Field(() => FileType)
  @IsEnum(FileType)
  type: typeof FileType;

  @Field(() => String)
  @IsString()
  relatedId: string;

  @Field(() => String)
  @IsString()
  relatedType: string;

  @Field(() => Status)
  @IsOptional()
  @IsEnum(Status)
  status: typeof Status;

  @Field(() => Date)
  @IsOptional()
  @IsDateString()
  createdAt: Date;

  @Field(() => Date)
  @IsOptional()
  @IsDateString()
  updatedAt: Date;

  @Field(() => Date, { nullable: true })
  @IsOptional()
  @IsDateString()
  deletedAt?: Date | null;
}
