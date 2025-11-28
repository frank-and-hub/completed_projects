import { GoneException, Injectable, NotFoundException } from '@nestjs/common';
import { CreateUserDto, QueryUsersDto, UpdateUserDto } from './dto';
import { DbService } from 'src/database';
import { app } from 'src/auth/constants';
import { generatePassword } from 'src/common';
import { Status } from 'src/common/enums/prisma-enums';


@Injectable()
export class UsersService {
    constructor(private readonly db: DbService) { }
    private readonly selectedColumns = { 
        id: true, 
        firstName: true, 
        middleName: true, 
        lastName: true, 
        email: true, 
        phone: true, 
        isNotify: true, 
        deviceId: true, 
        dateOfBirth: true, 
        aggreTerm: true, 
        token: true, 
        refreshToken: true, 
        deviceType: true, 
        relationshipStatus: true, 
        gender: true, 
        roleId: true, 
        role: { select: { id: true, name: true, description: true } }, 
        status: true, 
        createdAt: true, 
        updatedAt: true, 
        deletedAt: true 
    };

    async create(createUserDto: CreateUserDto) {
        const hashedPassword = await generatePassword(createUserDto?.password ?? 'Klub@1234');
        const newUser = await this.db.user.create({ data: { ...createUserDto, password: hashedPassword } });

        return await this.findOne(newUser.id);
    }

    async findAll(query: QueryUsersDto) {
        const { role, search, page = app.page, limit = app.limit, orderBy = 'createdAt', status, direction = 'desc', gender, deviceType, relationshipStatus } = query;
        const take = parseInt(limit);
        const skip = (parseInt(page) - 1) * take;

        let roleId: string | undefined = undefined;

        if (role) {
            const roleData = await this.db.role.findFirst({ where: { id: role }, select: { id: true } });
            if (roleData) roleId = roleData.id;
        }

        const whereClause: any = {
            deletedAt: null,
            ...(status && { status }),
            ...(gender && { gender }),
            ...(deviceType && { deviceType }),
            ...(relationshipStatus && { relationshipStatus }),
            ...(roleId && { roleId }),
            ...(search && {
                OR: [
                    { firstName: { contains: search, mode: 'insensitive' } },
                    { lastName: { contains: search, mode: 'insensitive' } },
                    { email: { contains: search, mode: 'insensitive' } },
                    { phone: { contains: search, mode: 'insensitive' } },
                    { role: { name: { contains: search, mode: 'insensitive' } } },
                ],
            }),
        };

        const users = await this.db.user.findMany({
            where: whereClause,
            orderBy: { [orderBy]: direction },
            skip,
            take,
            select: this.selectedColumns,
        });

        const total = await this.db.user.count({ where: whereClause });

        return {
            data: users,
            pagination: {
                total,
                page: parseInt(page),
                limit: take,
                pages: Math.ceil(total / take),
            },
            title: 'Users List'
        };
    }

    async findOne(id: string) {
        const data = await this.db.user.findUnique({ where: { id }, select: this.selectedColumns });
        if (!data) throw new NotFoundException('User not found with the given ID');
        if (data.deletedAt) throw new GoneException('User has been deleted');
        // if (!data.status) throw new ForbiddenException('User is inactive');
        return data;
    }

    async update(id: string, updateUserDto: UpdateUserDto) {
        const { password, ...safeDto } = updateUserDto;
        await this.db.user.update({ where: { id }, data: { ...safeDto } });
        return await this.findOne(id);
    }

    async remove(id: string) {
        return this.db.user.update({ where: { id }, data: { deletedAt: new Date() } });
    }

    async removeAll() {
        await this.db.user.deleteMany({ where: { deletedAt: { not: null, }, }, });
        return { message: 'All soft-deleted users have been permanently deleted.' };
    }

    async getUserRole(userId: string) {
        const user = await this.db.user.findUnique({
            where: { id: userId },
            select: { 
                id: true, 
                firstName: true, 
                lastName: true, 
                email: true,
                role: { select: { id: true, name: true, description: true } }
            }
        });
        return user;
    }

    async updateUserRole(userId: string, roleId: string) {
        return this.db.user.update({
            where: { id: userId },
            data: { roleId },
            select: this.selectedColumns
        });
    }

    async getUsersByRole(roleName: string) {
        return this.db.user.findMany({
            where: {
                role: { name: roleName },
                status: Status.ACTIVE,
                deletedAt: null
            },
            select: this.selectedColumns
        });
    }

    async getUserPermissions(userId: string) {
        const user = await this.db.user.findUnique({
            where: { id: userId },
            include: {
                role: {
                    include: {
                        permissions: true
                    }
                },
                permissions: true
            }
        });
        return user;
    }

    async assignPermissionToUser(userId: string, permissionId: string) {
        return this.db.user.update({
            where: { id: userId },
            data: {
                permissions: {
                    connect: { id: permissionId }
                }
            },
            include: { permissions: true }
        });
    }

    async removePermissionFromUser(userId: string, permissionId: string) {
        return this.db.user.update({
            where: { id: userId },
            data: {
                permissions: {
                    disconnect: { id: permissionId }
                }
            },
            include: { permissions: true }
        });
    }
}