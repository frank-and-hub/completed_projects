// 'src/auth/strategy/jwt.strategy.ts

import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { ConfigService } from '@nestjs/config';
import { jwtConstants } from '../constants';
import { DbService } from '../../database';
import { permission } from 'process';

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy, jwtConstants.secret) {
    constructor(private config: ConfigService, private db: DbService) {
        super({
            jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
            // jwtFromRequest: ExtractJwt.fromExtractors([extractJwtFromCookie]),
            ignoreExpiration: false,
            secretOrKey: jwtConstants.secret,
        });
    }

    // Required method
    async validate(payload: { sub: string, email: string }) {
        const { sub: id, email } = payload;
        if (!id || !email) return null; // or throw new UnauthorizedException('Invalid token');
        const authUser = await this.db.user.findUnique({
            where: { id },
            select: { id: true, firstName: true, middleName: true, lastName: true, email: true, phone: true, isNotify: true, role: { select: { id: true, name: true } }, deviceType: true, deviceId: true, relationshipStatus: true, gender: true, dateOfBirth: true, status: true, createdAt: true, updatedAt: true, deletedAt: true },
        });
        if (!authUser) return null;
        // const rolePermissions = authUser?.role?.permissions.map(p => p.name) || [];
        // console.log('rolePermissions', { rolePermissions });
        // const userPermissions = authUser?.permissions.map(p => p.name) || [];
        // const allPermissions = [...new Set([...rolePermissions, ...userPermissions])]; // remove duplicates
        // return { ...authUser, permissions: allPermissions };
        return { ...authUser };
    }

}

const extractJwtFromCookie = (req: any) => {
    return req?.cookies?.accessToken; // Make sure you're using cookie-parser middleware
};
