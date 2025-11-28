import { Injectable, CanActivate, ExecutionContext, ForbiddenException } from '@nestjs/common';
import { DbService } from '../../database/db.service';

@Injectable()
export class BusinessOwnerGuard implements CanActivate {
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

    // Check if user owns the business
    const business = await this.prisma.business.findFirst({
      where: {
        id: businessId,
        owners: {
          some: {
            id: user.id,
          },
        },
      },
    });

    if (!business) {
      throw new ForbiddenException('You are not the owner of this business');
    }

    return true;
  }
}
