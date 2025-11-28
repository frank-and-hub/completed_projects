import { ConflictException, ForbiddenException, Injectable, UnauthorizedException } from '@nestjs/common';
import { generateRandomPhoneNumber, generateRandomString } from '../common'
import { ConfigService } from '@nestjs/config';
import { JwtService } from '@nestjs/jwt';
import { OtpDto, SigninDto, SignupDto } from './dto';
import { DbService } from 'src/database';
import { jwtConstants } from './constants';
import * as argon from 'argon2';
import { EmailService } from 'src/email/email.service';

@Injectable()
export class AuthService {
    constructor(
        private readonly db: DbService,
        private jwt: JwtService,
        private config: ConfigService,
        private sendEmail: EmailService,
    ) { }

    async login(
        req: SigninDto,
    ): Promise<{ access_token: string }> {
        const { email, password, rememberMe } = req;
        const user = await this.db.user.findUnique({ where: { email }, select: { id: true, email: true, roleId: true, password: true } });
        if (!user) throw new ForbiddenException('Credentials Invalid');

        const isPasswordValid = await argon.verify(user.password, password);
        if (!isPasswordValid) throw new UnauthorizedException();

        const token = await this.generateToken(user.id, user.email, rememberMe);
        return { access_token: token.accessToken };
    }

    async signup(
        req: SignupDto,
    ): Promise<{ access_token: string }> {
        console.log(`This is req:`, req);
        const { email, password, aggreTerm, name, dialCode, phone, confirmPassword } = req;
        if (confirmPassword !== password) throw new ConflictException('Passwords do not match');

        const userRole = await this.db.role.findFirst({ where: { name: 'User' }, select: { id: true } });
        if (!userRole) throw new ConflictException('User role not found');

        const existsUser = await this.db.user.findFirst({ where: { email } });
        if (existsUser) throw new ConflictException('User already exists');

        const hashedPassword = await this.generatePassword(password);

        const user = await this.db.user.create({
            data: {
                email,
                dialCode,
                phone,
                password: hashedPassword,
                firstName: name,
                role: {
                    connect: { id: userRole.id }
                },
                aggreTerm: aggreTerm,
            },
            select: {
                id: true,
                email: true,
                firstName: true
            }
        });
        const token = await this.generateToken(user.id, user.email);
        await this.sendEmail.sendWelcomeEmail({ email: user.email, name: user.firstName });
        return { access_token: token.accessToken };
    }

    // async otpVerifying(
    //     req:OtpDto
    // ): Promise<{ access_token: string }> {
    //     const { otp } = req;

    //     const user = await this.db.user.findUnique({ where: { otp }, select: { id: true, email: true, roleId: true, password: true } });
    //     if (!user) throw new ForbiddenException('One Time Password is miss-matched');

    //     const token = await this.generateToken(user.id, user.email);
    //     return { access_token: token.accessToken };
    // }

    // helpers

    async generatePassword(string: string): Promise<string> {
        return await argon.hash(string);
    }

    async generateToken(id: string, email: string, rememberMe: boolean = false) {
        const payload = { sub: id, email: email, jwtSecret: generateRandomString(80), phone: generateRandomPhoneNumber() };

        const accessToken = await this.jwt?.signAsync(payload, {
            secret: jwtConstants.secret,
            expiresIn: rememberMe ? '30d' : jwtConstants.expires
        });

        const refreshToken = await this.jwt?.signAsync(payload, {
            secret: jwtConstants.refresh_secret,
            expiresIn: jwtConstants.expires
        });

        const hashedRefresh = await this.generatePassword(refreshToken);

        await this.db.user.update({
            where: { id: payload.sub },
            data: { token: accessToken, refreshToken: hashedRefresh },
        });

        return { accessToken, refreshToken };
    }

    // async genarteRefreshToken() { }

    async verifyToken(token: string) {
        try {
            return await this.jwt.verifyAsync(token, {
                secret: jwtConstants.secret,
            });
        } catch (err) {
            throw new UnauthorizedException('Invalid or expired access token');
        }
    }

    async verifyRefreshToken(userId: string, refreshToken: string) {
        const user = await this.db.user.findUnique({
            where: { id: userId },
        });

        if (!user?.refreshToken)
            throw new UnauthorizedException('Refresh token not found');

        const isValid = await argon.verify(user.refreshToken, refreshToken);
        if (!isValid) throw new UnauthorizedException('Invalid refresh token');

        return user;
    }

    async revokeToken(_token: string) {
        return true;
    }

    async revokeRefreshToken(userId: string) {
        await this.db.user.update({
            where: { id: userId },
            data: { refreshToken: null },
        });
    }

    async sendVerificationEmail(email: string, token: string) {
        const url = `${this.config.get('FRONTEND_URL')}/verify?token=${token}`;
        console.log(`[Email] Verify account: ${url}`);
    }

    async sendResetPasswordEmail(email: string, token: string) {
        const url = `${this.config.get('FRONTEND_URL')}/reset-password?token=${token}`;
        console.log(`[Email] Reset password link: ${url}`);
    }

    async resetPassword(email: string, newPassword: string) {
        const hashed = await this.generatePassword(newPassword);
        return await this.db.user.update({
            where: { email },
            data: { password: hashed },
        });
    }

    async changePassword(userId: string, oldPassword: string, newPassword: string) {
        const user = await this.db.user.findUnique({ where: { id: userId } });
        if (!user) throw new UnauthorizedException('User not found');

        const isMatch = await argon.verify(user.password, oldPassword);
        if (!isMatch) throw new UnauthorizedException('Old password is incorrect');

        const hashed = await this.generatePassword(newPassword);
        return this.db.user.update({
            where: { id: userId },
            data: { password: hashed },
        });
    }

    // social logins
    // async googleLogin() { }

    // async appleLogin() { }

    // async facebookLogin() { }

    // async githubLogin() { }

    // async twitterLogin() { }

    // async linkedinLogin() { }

    // async instagramLogin() { }

    // async discordLogin() { }
}
