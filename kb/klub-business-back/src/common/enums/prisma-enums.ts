// src/common/enums/prisma-enums.ts

import { registerEnumType } from '@nestjs/graphql';
import * as Prisma from '@prisma/client';

// Extract only enums from Prisma client
const enumEntries = Object.entries(Prisma).filter(([_, value]) => {
  return (
    typeof value === 'object' &&
    value !== null &&
    Object.values(value).every((v) => typeof v === 'string')
  );
});

// Export object to hold all enums
const PrismaEnums: Record<string, any> = {};

for (const [name, enumObj] of enumEntries) {
  // Register enum with GraphQL
  registerEnumType(enumObj, {
    name,
    description: `Prisma enum: ${name}`,
  });

  // Attach to exported object
  PrismaEnums[name] = enumObj;
}

// Export all enums as named exports for cleaner usage
export const {
  Gender,
  RelationshipStatus,
  PlanType,
  PaymentMethod,
  PaymentStatus,
  NotificationType,
  NotificationCategory,
  MessageType,
  FileType,
  FriendshipStatus,
  ReportStatus,
  DeviceType,
  DurationType,
  Status,
  PriorityType,
  VerificationStatus,
  EmployeeRole,
  EmployeeStatus,
  AttendanceStatus,
  EmployeeShiftType,
  LeaveType
} = PrismaEnums;

export enum SortDirection {
    asc = 'asc',
    desc = 'desc',
}