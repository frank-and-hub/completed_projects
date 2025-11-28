import { Global, Module } from '@nestjs/common';
import { DbService } from './db.service';
import { WinstonModule } from 'nest-winston';
import { transports, format } from 'winston';

@Global()
@Module({
  imports: [
    WinstonModule.forRoot({
      transports: [
        new transports.Console({
          format: format.combine(
            format.timestamp(),
            format.colorize(),
            format.simple()
          ),
        }),
        new transports.File({
          filename: 'logs/app.log',
          level: 'info',
          format: format.combine(
            format.timestamp(),
            format.json()
          ),
        }),
        new transports.File({
          filename: 'logs/error.log',
          level: 'error',
          format: format.combine(
            format.timestamp(),
            format.json()
          ),
        }),
      ],
    }),
  ],
  providers: [DbService],
  exports: [DbService, WinstonModule],
})
export class DatabaseModule { }