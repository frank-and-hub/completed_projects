import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

export async function createCurrency() {
    await prisma.currency.deleteMany();
    await prisma.currency.createMany({
        data: [
            { name: 'United States Dollar', shortName: 'USD', symbol: '$' },
            { name: 'Euro', shortName: 'EUR', symbol: '€' },
            { name: 'British Pound Sterling', shortName: 'GBP', symbol: '£' },
            { name: 'Japanese Yen', shortName: 'JPY', symbol: '¥' },
            { name: 'Indian Rupee', shortName: 'INR', symbol: '₹' },
            { name: 'Australian Dollar', shortName: 'AUD', symbol: 'A$' },
            { name: 'Canadian Dollar', shortName: 'CAD', symbol: 'C$' },
            { name: 'Swiss Franc', shortName: 'CHF', symbol: 'Fr' },
            { name: 'Chinese Yuan', shortName: 'CNY', symbol: '¥' },
            { name: 'South African Rand', shortName: 'ZAR', symbol: 'R' }
        ],
        skipDuplicates: true,
    });

    console.log('Seeded currencies');
}