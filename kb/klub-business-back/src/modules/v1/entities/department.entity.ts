import { Field, ObjectType } from "@nestjs/graphql";
import { BusinessCategoryEntity } from "src/business-category/entities/business-category.entity";
import { Status } from "src/common/enums/prisma-enums";

@ObjectType()
export class DepartmentEntity {
    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;

    @Field(() => String, { nullable: true })
    description?: string | null;

    @Field(() => BusinessCategoryEntity)
    businessCategory: BusinessCategoryEntity;

    @Field(() => Status)
    status: typeof Status;

    @Field(() => Date)
    createdAt: Date;

    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, { nullable: true })
    deletedAt?: Date | null;
}
