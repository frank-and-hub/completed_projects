import { Injectable, OnModuleInit } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { PrismaClient } from '@prisma/client';
// import { PrismaClient } from 'generated/prisma';

@Injectable()
// export class DbService extends PrismaClient implements OnModuleInit {
//     constructor(config: ConfigService) {
//         super({});
//     }
//     async onModuleInit() {
//         await this.$connect();
//     }
// } { }

export class DbService extends PrismaClient implements OnModuleInit {
    constructor(config: ConfigService) {
        super({
            datasources: {
                db: {
                    url: config.get('DATABASE_URL')
                }
            }
        });
    }

    async onModuleInit() {
        await this.$connect();
    }

    async onModuleDestroy() {
        await this.$disconnect();
    }

    async cleanDb() {
        await this.$transaction(async (db) => {
            // await db.user.deleteMany();
            // await db.role.deleteMany({ where: { name: { not: 'User' } } });
            // await db.currency.deleteMany({ where: { name: { not: 'Indian Rupee' } } });
        });
    }
}