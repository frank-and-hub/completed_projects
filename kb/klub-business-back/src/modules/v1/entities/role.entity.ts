import { Status } from "src/common/enums/prisma-enums";
import { ObjectType } from "@nestjs/graphql";
import { Field } from "@nestjs/graphql";

@ObjectType()
export class RoleEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;

    @Field(() => String, { nullable: true })
    description?: string | null;

    @Field(() => Status)
    status: typeof Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
