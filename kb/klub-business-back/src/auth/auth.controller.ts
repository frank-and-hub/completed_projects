import { Body, Controller, HttpCode, HttpStatus, Post } from '@nestjs/common';
import { AuthService } from './auth.service';
import { SignupDto, SigninDto } from './dto';
import { Public } from 'src/common/decorators/public.decorator';

@Public()
@Controller({ path: 'auth', version: '1' })
export class AuthController {
    constructor(private authService: AuthService) { }

    @HttpCode(HttpStatus.OK)
    @Post('sign-up')
    signup(@Body() signupDto: SignupDto) {
        return this.authService.signup(signupDto)
    }

    @HttpCode(HttpStatus.OK)
    @Post('sign-in')
    signin(@Body() signinDto: SigninDto) {
        return this.authService.login(signinDto)
    }

}