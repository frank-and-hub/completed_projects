import { Status } from "@prisma/client";

export class BusinessStaticPageEntity {
    id: string;
    name: string;
    description?: string | null;
    business: { id: string, name: string };
    status: Status;
    createdAt: Date;
    updatedAt: Date;
    deletedAt?: Date | null;
}
