import { Field, ObjectType } from "@nestjs/graphql";
import { Status } from "src/common/enums/prisma-enums";

@ObjectType()
export class BusinessCategoryEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;   

    @Field(() => String)
    description: string;

    @Field(() => Status)
    status: typeof Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
