import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

export async function createBusinessCategory() {
    await prisma.businessCategory.deleteMany();
    await prisma.businessCategory.createMany({
        data: [
            { name: 'Hotel', description: 'Hospitality and accommodation services' },
            { name: 'Club', description: 'Private membership-based entertainment venues' },
            { name: 'Hospital', description: 'Healthcare and medical services' },
            { name: 'School', description: 'Educational institution' },
            { name: 'Bank', description: 'Financial and banking services' },
            { name: 'Restaurant', description: 'Food and beverage services' },
            { name: 'Gym', description: 'Fitness and wellness centers' },
            { name: 'Salon', description: 'Personal grooming and beauty services' },
            { name: 'Cinema', description: 'Film and entertainment businesses' },
            { name: 'Coworking Space', description: 'Shared workspaces for professionals' },
            { name: 'Retail Store', description: 'Physical stores selling consumer goods' },
            { name: 'Event Venue', description: 'Locations for hosting events and gatherings' },
        ],
        skipDuplicates: true,
    });

    console.log('Seeded roles');
}