import { Field, ObjectType } from '@nestjs/graphql';
import { UserEntity } from '../../entities/user.entity';

@ObjectType()
export class UserResponse {
    @Field()
    ok: boolean;

    @Field({ nullable: true })
    message?: string;

    @Field(() => UserEntity, { nullable: true })
    user?: UserEntity;
}
