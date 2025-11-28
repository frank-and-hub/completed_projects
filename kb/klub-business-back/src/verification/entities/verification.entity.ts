import { VerificationStatus, Status } from 'src/common/enums/prisma-enums';
import { Field, ObjectType } from "@nestjs/graphql";

@ObjectType()
export class Verification {
  @Field(() => String)
  id: string;

  @Field(() => String)
  userId: string;

  @Field(() => String, { nullable: true })
  code?: string | null;

  @Field(() => Date, { nullable: true })
  expiredAt?: Date | null;

  @Field(() => VerificationStatus, { defaultValue: VerificationStatus.PENDING })
  verificationStatus?: typeof VerificationStatus;

  @Field(() => Boolean, { defaultValue: false })
  isEmailVarified: boolean;

  @Field(() => Boolean, { defaultValue: false })
  isPhoneVarified: boolean;

  @Field(() => String)
  status: typeof Status;

  @Field(() => Date)
  createdAt: Date;

  @Field(() => Date)
  updatedAt: Date;

  @Field(() => Date, { nullable: true })
  deletedAt?: Date | null;
}
