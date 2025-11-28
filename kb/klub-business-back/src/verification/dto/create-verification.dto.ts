import { IsString, IsEnum, IsBoolean, IsDateString, IsOptional } from 'class-validator';
import { VerificationStatus } from 'src/common/enums/prisma-enums';
import { Field, InputType } from '@nestjs/graphql';

@InputType()
export class CreateVerificationDto {
  @Field(() => String)
  @IsString()
  userId: string;

  @Field(() => String)
  @IsString()
  code: string;

  @Field(() => Date)
  @IsDateString()
  expiredAt: string;

  @Field(() => VerificationStatus, { nullable: true })
  @IsOptional()
  @IsEnum(VerificationStatus)
  verificationStatus?: typeof VerificationStatus;

  @Field(() => Boolean, { defaultValue: false })
  @IsOptional()
  @IsBoolean()
  isEmailVarified?: boolean;

  @Field(() => Boolean, { defaultValue: false })
  @IsOptional()
  @IsBoolean()
  isPhoneVarified?: boolean;

  @Field(() => Date, { nullable: true })
  @IsOptional()
  @IsDateString()
  deletedAt?: string;
}
