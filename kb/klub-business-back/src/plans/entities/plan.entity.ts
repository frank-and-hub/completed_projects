import { Field, ObjectType } from '@nestjs/graphql';
import {DurationType, PlanType, Status } from 'src/common/enums/prisma-enums';
import { CurrencyEntity } from 'src/currency/entities/currency.entity';

@ObjectType()
export class PlanEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;

    @Field(() => String, { nullable: true })
    description?: string | null;

    @Field(() => Number, { nullable: true })
    amount?: number | null;

    @Field(() => CurrencyEntity)
    currency: CurrencyEntity;

    @Field(() => PlanType)
    planType: typeof PlanType;

    @Field(() => Number, { nullable: true })
    duration?: number;

    @Field(() => DurationType)
    durationType: typeof DurationType;
    // durationType: DurationType;

    @Field(() => Status)
    status: typeof Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
