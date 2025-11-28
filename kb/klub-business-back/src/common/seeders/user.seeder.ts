import { Prisma, PrismaClient } from "@prisma/client";
import * as argon from 'argon2';
import { generateRandomNumber, generateRandomString, getRandomDate, getRandomNumberGreaterThan } from "../";
const prisma = new PrismaClient();

export async function createUsers({ usersPerRole }: { usersPerRole: number }) {
    const roles = ['Supper Admin', 'Admin', 'Owner', 'Employee', 'User', 'Tester'];
    const password = await argon.hash('Klub@1234');

    for (const roleName of roles) {
        const role = await prisma.role.findFirst({ where: { name: roleName } });
        if (!role) continue;

        const users: Prisma.UserCreateManyInput[] = [];

        for (let i = 0; i < usersPerRole; i++) {
            const firstName = generateRandomString(8);
            const middleName = generateRandomString(9);
            const lastName = generateRandomString(10);
            const phone = generateRandomNumber(getRandomNumberGreaterThan());
            const email = `${firstName.toLowerCase()}.${lastName.toLowerCase()}@yopmail.com`;
            const deviceId = generateRandomString(25);
            const dateOnBirth = getRandomDate(new Date(1970, 1, 1), new Date(2000, 1, 1));
            const dialCode = '+1';

            users.push({ firstName, middleName, lastName, email, dialCode, phone, password, roleId: role.id, deviceId: deviceId, dateOfBirth: dateOnBirth });
        }

        await prisma.user.createMany({
            data: users,
            skipDuplicates: true,
        });

        console.log(`Seeded ${usersPerRole} users for role: ${roleName}`);
    }

    console.log('All users seeded successfully');
}


export async function createStaticUsers() {
    await prisma.user.deleteMany();
    const password = await argon.hash('password');

    const roles = await prisma.role.findMany({
        where: {
            name: { in: ['Supper Admin', 'Admin', 'Owner', 'Employee', 'User'] }
        },
        select: { id: true, name: true },
    });

    const getRoleId = (roleName: string) => roles.find(r => r.name === roleName)?.id;

    const users = [
        { firstName: 'Supper', lastName: 'Admin', email: 'supper.admin@yopmail.com', password, roleId: getRoleId('Supper Admin'), },
        { firstName: 'Admin', lastName: 'User', email: 'admin.user@gmail.com', password, roleId: getRoleId('Admin'), },
        { firstName: 'Owner', lastName: 'User', email: 'owner.user@gmail.com', password, roleId: getRoleId('Owner'), },
        { firstName: 'Employee', lastName: 'User', email: 'employee.user@gmail.com', password, roleId: getRoleId('Employee'), },
        { firstName: 'Customer', lastName: 'User', email: 'customer.user@gmail.com', password, roleId: getRoleId('User'), }
    ];

    for (const user of users) {
        if (!user.roleId) {
            console.warn(`Role not found for user: ${user.email}`);
            continue;
        }
        const phone = generateRandomNumber(getRandomNumberGreaterThan());
        const dialCode = '+91';

        // await prisma.user.upsert({
        //     where: { email: user.email },
        //     update: {},
        //     create: user,
        // });
        await prisma.user.create({ data: { ...user, phone, dialCode } });
    }

    console.log('Seeded users');
}
