import { Status } from "@prisma/client";
import { Field, ObjectType } from "@nestjs/graphql";

@ObjectType()
export class CountryEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;

    @Field(() => String)
    iso2: string;

    @Field(() => String)
    iso3: string;

    @Field(() => Status)
    status: Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
