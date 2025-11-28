import {
  CanActivate,
  ExecutionContext,
  Injectable,
  ForbiddenException,
} from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { PERMISSIONS_KEY } from '../decorator';

@Injectable()
export class PermissionsGuard implements CanActivate {
  constructor(private reflector: Reflector) {}

  canActivate(context: ExecutionContext): boolean {
    const requiredPermissions = this.reflector.getAllAndOverride<string[]>(
      PERMISSIONS_KEY,
      [context.getHandler(), context.getClass()],
    );

    if (!requiredPermissions) return true;

    const request = context.switchToHttp().getRequest();
    const user = request.user;

    if (!user?.permissions || !Array.isArray(user.permissions)) {
      throw new ForbiddenException('User permissions not found');
    }

    const hasPermission = requiredPermissions.every(p =>
      user.permissions.includes(p),
    );

    if (!hasPermission) {
      throw new ForbiddenException('Access denied');
    }

    return true;
  }
}
