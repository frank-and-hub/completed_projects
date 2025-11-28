import { Injectable, CanActivate, ExecutionContext, ForbiddenException } from '@nestjs/common';
import { DbService } from '../../database/db.service';
import { Status } from '../../common/enums/prisma-enums';

@Injectable()
export class EmployeeGuard implements CanActivate {
  constructor(private prisma: DbService) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    const businessId = request.params.businessId || request.body.businessId;

    if (!user) {
      throw new ForbiddenException('User not authenticated');
    }

    if (!businessId) {
      throw new ForbiddenException('Business ID is required');
    }

    // Check if user is an employee of the business
    const employee = await this.prisma.employee.findFirst({
      where: {
        userId: user.id,
        businesses: {
          some: {
            id: businessId,
          },
        },
        status: Status.ACTIVE,
      },
    });

    if (!employee) {
      throw new ForbiddenException('You are not an employee of this business');
    }

    // Add employee info to request for use in controllers
    request.employee = employee;
    return true;
  }
}
