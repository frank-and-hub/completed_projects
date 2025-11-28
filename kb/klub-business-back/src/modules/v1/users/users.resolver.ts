import { Resolver, Query, Args, Mutation } from '@nestjs/graphql';
import { UseGuards } from '@nestjs/common';
import { UsersService } from './users.service';
import { CreateUserDto, UpdateUserDto, UserResponse } from './dto';
import { UserEntity } from '../entities/user.entity';
import { GqlAuthGuard } from './guards/gql-auth.guard';
import { Public } from 'src/decorators/public.decorator';

@Resolver(() => UserEntity)
export class UsersResolver {
    constructor(private readonly usersService: UsersService) { }

    @Query(() => [UserEntity])
    @Public()
    async users() {
        try {
            const result = await this.usersService.findAll({});
            return result.data;
        } catch (error) {
            throw new Error(`Failed to fetch users: ${error.message}`);
        }
    }

    @Query(() => UserEntity)
    @Public()
    async user(@Args('id') id: string) {
        try {
            return await this.usersService.findOne(id);
        } catch (error) {
            throw new Error(`Failed to fetch user: ${error.message}`);
        }
    }

    @Mutation(() => UserResponse)
    @UseGuards(GqlAuthGuard)
    async createUser(@Args('input') input: CreateUserDto) {
        try {
            const user = await this.usersService.create(input);
            return {
                ok: true,
                message: 'User created successfully',
                user
            };
        } catch (error) {
            return {
                ok: false,
                message: `Failed to create user: ${error.message}`,
                user: null
            };
        }
    }

    @Mutation(() => UserResponse)
    @UseGuards(GqlAuthGuard)
    async updateUser(
        @Args('id') id: string,
        @Args('input') input: UpdateUserDto
    ) {
        try {
            const user = await this.usersService.update(id, input);
            return {
                ok: true,
                message: 'User updated successfully',
                user
            };
        } catch (error) {
            return {
                ok: false,
                message: `Failed to update user: ${error.message}`,
                user: null
            };
        }
    }

    @Mutation(() => UserResponse)
    @UseGuards(GqlAuthGuard)
    async deleteUser(@Args('id') id: string) {
        try {
            await this.usersService.remove(id);
            return {
                ok: true,
                message: 'User deleted successfully',
                user: null
            };
        } catch (error) {
            return {
                ok: false,
                message: `Failed to delete user: ${error.message}`,
                user: null
            };
        }
    }
}
