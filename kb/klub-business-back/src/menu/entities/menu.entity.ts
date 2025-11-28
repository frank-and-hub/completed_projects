import { Status } from "@prisma/client";

export class MenuEntity {
    id: string;
    name: string;
    slug: string;
    route?: string | undefined;
    type: boolean;
    icon?: string | undefined;
    // parent?: { select: { id: string, name: string; } };
    // children?: { select: { id: string, name: string; } };
    status: Status;
    createdAt: Date;
    updatedAt: Date;
}
