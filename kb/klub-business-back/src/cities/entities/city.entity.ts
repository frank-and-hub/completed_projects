import { Field, ObjectType } from "@nestjs/graphql";
import { Status } from "@prisma/client";
import { CountryEntity } from "src/countries/entities/country.entity";
import { StateEntity } from "src/states/entities/state.entity";

@ObjectType()
export class CityEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;

    @Field(() => StateEntity)
    state: { id: string, name: string };

    @Field(() => CountryEntity)
    country: { id: string, name: string };

    @Field(() => Status)
    status: Status;

    @Field(() => Date)  
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
