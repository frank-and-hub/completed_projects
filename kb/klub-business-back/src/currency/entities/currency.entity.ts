import { Field, InputType } from "@nestjs/graphql";
import { Status } from "src/common/enums/prisma-enums";

@InputType()
export class CurrencyEntity {

    @Field(() => String)
    id: string;

    @Field(() => String)
    name: string;
    
    @Field(() => String)
    shortName?: string | null;
    
    @Field(() => String)
    symbol?: string | null;
    
    @Field(() => Status)
    status: typeof Status;

    @Field(() => Date)
    createdAt: Date;
    
    @Field(() => Date)
    updatedAt: Date;

    @Field(() => Date, {nullable:true})
    deletedAt?: Date | null;
}
