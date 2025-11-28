import { DurationType, PriorityType } from '@prisma/client';

export class Task {
  id: string;
  businessId: string;
  assignedToId?: string | null;
  assignedById: string;
  title: string;
  description: string;
  durationType: DurationType;
  priority: PriorityType;
  deadline: Date;
  isCompleted: boolean;
  status: string;
  createdAt: Date;
  updatedAt: Date;
  deletedAt?: Date | null;
}
