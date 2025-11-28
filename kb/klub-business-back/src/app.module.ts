import { Module } from '@nestjs/common';
// import { GraphQLModule } from '@nestjs/graphql';
// import { ApolloDriverConfig, ApolloDriver } from '@nestjs/apollo';
import { ConfigModule } from '@nestjs/config';
import { AppController } from 'src/app.controller';
import { AppService } from 'src/app.service';
import { AuthModule } from 'src/auth/auth.module';
import { UsersModule } from 'src/modules/v1/users/users.module';
import { DatabaseModule } from 'src/database/db.module';
import { CitiesModule } from 'src/cities/cities.module';
import { StatesModule } from 'src/states/states.module';
import { CountriesModule } from 'src/countries/countries.module';
import { DepartmentsModule } from 'src/modules/v1/departments/departments.module';
import { PlansModule } from 'src/plans/plans.module';
import { FilesModule } from 'src/files/files.module';
import { JwtService } from '@nestjs/jwt';
import { BusinessCategoryModule } from 'src/business-category/business-category.module';
import { CurrencyModule } from 'src/currency/currency.module';
import { ThrottlerModule, ThrottlerGuard } from '@nestjs/throttler';
import { APP_GUARD } from '@nestjs/core';
import { BusinessStaticPageModule } from 'src/business-static-page/business-static-page.module';
import { RolesModule } from 'src/modules/v1/roles/roles.module';
import { CommonDataModule } from 'src/common_data/common_data.module';
import { CacheModule } from '@nestjs/cache-manager';
import { redisStore } from 'cache-manager-redis-yet';
import { ChatModule } from 'src/chat/chat.module';
import { EmailModule } from 'src/email/email.module';
import { BullModule } from '@nestjs/bull';
import { PaymentModule } from './payment/payment.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    ThrottlerModule.forRoot({
      throttlers: [
        {
          ttl: 1000,
          limit: 60,
        },
      ],
    }),
    CacheModule.register({
      isGlobal: true,
      ttl: 60 * 1000,
      store: redisStore,
    }),
    BullModule.forRoot({
      redis: {
        host: process.env.REDIS_HOST ?? `localhost`,
        port: process.env.REDIS_PORT ? parseInt(process.env.REDIS_PORT) : 6379,
      },
      defaultJobOptions: {
        attempts: 3,
        backoff: {
          type: 'exponential',
          delay: 1000,
        },
      },
    }),
    /*
    GraphQLModule.forRoot<ApolloDriverConfig>({
      driver: ApolloDriver,
      autoSchemaFile: true,
      sortSchema: true,
      // cors: true,
      debug: process.env.NODE_ENV !== 'production',
      playground: process.env.NODE_ENV !== 'production',
      introspection: process.env.NODE_ENV !== 'production',
      context: ({ req }) => ({ req }),
      // plugins: process.env.NODE_ENV !== 'production' 
      //   ? [
      //       ApolloServerPluginLandingPageLocalDefault(),
      //       ApolloServerPluginInlineTrace()
      //     ] 
      //   : [
      //       ApolloServerPluginInlineTrace()
      //     ],
      formatError: (error) => {
        if (process.env.NODE_ENV !== 'production') {
          console.error('GraphQL Error:', error);
        }

        return {
          message: error.message,
          code: error.extensions?.code || 'INTERNAL_ERROR',
          ...(process.env.NODE_ENV !== 'production' && {
            path: error.path,
          }),
        };
      },
    }),
    */
    AuthModule,
    UsersModule,
    DatabaseModule,
    CitiesModule,
    StatesModule,
    CountriesModule,
    RolesModule,
    DepartmentsModule,
    PlansModule,
    FilesModule,
    BusinessCategoryModule,
    CurrencyModule,
    BusinessStaticPageModule,
    CommonDataModule,
    ChatModule,
    EmailModule,
    PaymentModule,
  ],
  controllers: [AppController],
  providers: [
    AppService,
    JwtService,
    {
      provide: APP_GUARD,
      useClass: ThrottlerGuard
    }
  ],
})
export class AppModule { }
