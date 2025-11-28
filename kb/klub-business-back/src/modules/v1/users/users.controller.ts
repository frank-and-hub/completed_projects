import { Body, Controller, Delete, Get, Param, Patch, Post, Put, Query, ParseUUIDPipe, UseGuards } from '@nestjs/common';
import { CreateUserDto, UpdateUserDto, QueryUsersDto } from './dto';
import { UsersService } from './users.service';
import { JwtGuard } from 'src/auth/guards';
import { GetUser } from 'src/auth/decorator';
import { UserEntity } from '../entities';

@UseGuards(JwtGuard)
@Controller({ path: 'users', version: '1' })
export class UsersController {
    constructor(private readonly usersService: UsersService) { }

    @Get()
    index(@Query() query: QueryUsersDto) {
        return this.usersService.findAll(query);
    }

    @Post()
    create(@Body() createUserDto: CreateUserDto): Promise<UserEntity> {
        return this.usersService.create(createUserDto);
    }

    @Get('profile')
    profile(@GetUser() user: any): Promise<UserEntity> {
        return this.usersService.findOne(user.id);
    }

    @Get(':id')
    findOne(@Param('id', ParseUUIDPipe) id: string): Promise<UserEntity> {
        return this.usersService.findOne(id);
    }

    @Put(':id')
    replace(@Param('id', ParseUUIDPipe) id: string, @Body() updateUserDto: UpdateUserDto): Promise<UserEntity> {
        return this.usersService.update(id, updateUserDto);
    }

    @Patch(':id')
    update(@Param('id', ParseUUIDPipe) id: string, @Body() updateUserDto: UpdateUserDto): Promise<UserEntity> {
        return this.usersService.update(id, updateUserDto);
    }

    @Delete(':id')
    remove(@Param('id', ParseUUIDPipe) id: string) {
        return this.usersService.remove(id);
    }

    @Delete()
    removeAll() {
        return this.usersService.removeAll();
    }

    @Get(':id/role')
    getUserRole(@Param('id', ParseUUIDPipe) id: string) {
        return this.usersService.getUserRole(id);
    }

    @Patch(':id/role')
    updateUserRole(@Param('id', ParseUUIDPipe) id: string, @Body() body: { roleId: string }) {
        return this.usersService.updateUserRole(id, body.roleId);
    }

    @Get('role/:roleName')
    getUsersByRole(@Param('roleName') roleName: string) {
        return this.usersService.getUsersByRole(roleName);
    }

    @Get(':id/permissions')
    getUserPermissions(@Param('id', ParseUUIDPipe) id: string) {
        return this.usersService.getUserPermissions(id);
    }

    @Post(':id/permissions')
    assignPermissionToUser(@Param('id', ParseUUIDPipe) id: string, @Body() body: { permissionId: string }) {
        return this.usersService.assignPermissionToUser(id, body.permissionId);
    }

    @Delete(':id/permissions/:permissionId')
    removePermissionFromUser(@Param('id', ParseUUIDPipe) id: string, @Param('permissionId', ParseUUIDPipe) permissionId: string) {
        return this.usersService.removePermissionFromUser(id, permissionId);
    }
}
