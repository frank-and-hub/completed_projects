import { Field, ID, ObjectType } from "@nestjs/graphql";
import { DeviceType, Gender, RelationshipStatus, Status } from "@prisma/client";
import { CityEntity } from "src/cities/entities";
import { CountryEntity } from "src/countries/entities";
import { StateEntity } from "src/states/entities";
import { DepartmentEntity, RoleEntity } from "src/modules/v1/entities";

@ObjectType()
export class UserEntity {
    @Field(() => ID)
    id: string;

    @Field(() => String, { nullable: true })
    firstName?: string | null;

    @Field(() => String, { nullable: true })
    middleName?: string | null;

    @Field(() => String, { nullable: true })
    lastName?: string | null;

    @Field(() => String, { nullable: false })
    email: string;

    @Field(() => String, { nullable: true })
    phone?: string | null;

    @Field(() => Boolean, { defaultValue: true })
    isNotify: boolean;

    @Field(() => CityEntity, { nullable: true })
    city?: { id: string, name: string } | null;

    @Field(() => StateEntity, { nullable: true })
    state?: { id: string, name: string } | null;

    @Field(() => CountryEntity, { nullable: true })
    country?: { id: string, name: string } | null;

    @Field(() => RoleEntity, { nullable: true })
    role?: { id: string, name: string } | null;

    @Field(() => DepartmentEntity, { nullable: true })
    department?: { id: string, name: string } | null;

    @Field(() => String, { nullable: true })
    deviceId?: string | null;

    @Field(() => Date, { nullable: true })
    dateOfBirth?: Date | null;

    @Field(() => Boolean, { nullable: true })
    aggreTerm: boolean;

    @Field(() => DeviceType, { nullable: true })
    deviceType?: DeviceType | null;

    @Field(() => RelationshipStatus, { nullable: true })
    relationshipStatus?: RelationshipStatus | null;

    @Field(() => Gender, { nullable: true })
    gender?: Gender | null;

    @Field(() => Status, { nullable: true })
    status: Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
