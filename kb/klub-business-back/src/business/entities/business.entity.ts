import { Status } from "@prisma/client";

export class BusinessEntity {
    id: string;
    name: string;
    description?: string | null;
    phone?: string | null;
    isVerified: boolean
    latitude?: string | null;
    longitude?: string | null;
    businessCategory: { id: string, name: string };
    city?: { id: string, name: string } | null;
    state?: { id: string, name: string } | null;
    country?: { id: string, name: string } | null;
    status: Status;
    createdAt: Date;
    updatedAt: Date;
    deletedAt?: Date | null;
}
