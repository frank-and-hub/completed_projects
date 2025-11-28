import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

export async function createPermissions() {
    await prisma.permission?.deleteMany();
    await prisma.permission.createMany({
        data: [
            { name: 'read_user' },
            { name: 'create_user' },
            { name: 'update_user' },
            { name: 'remove_user' },
            { name: 'read_permission' },
            { name: 'create_permission' },
            { name: 'update_permission' },
            { name: 'remove_permission' },
        ],
        skipDuplicates: true,
    });

    console.log('Seeded permissions');
}