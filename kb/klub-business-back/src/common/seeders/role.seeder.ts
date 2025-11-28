import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

export async function createRoles() {
    await prisma.role.deleteMany();
    await prisma.role.createMany({
        data: [
            { name: 'Supper Admin', description: 'Administrator' },
            { name: 'Admin', description: 'Admin user' },
            { name: 'Owner', description: 'Business owner user' },
            { name: 'Employee', description: 'Business employee user' },
            { name: 'User', description: 'Regular user' },
            { name: 'Tester', description: 'Tester user' },
        ],
        skipDuplicates: true,
    });

    console.log('Seeded roles');
}