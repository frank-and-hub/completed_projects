import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { ValidationPipe, VersioningType } from '@nestjs/common';
import helmet from 'helmet';
import cluster from 'cluster';
import * as os from 'os';
import compression from 'compression';
import { AllExceptionsFilter, PrismaExceptionFilter } from 'src/common/filters/prisma-exception.filter';
import { FastifyAdapter, NestFastifyApplication } from '@nestjs/platform-fastify';

async function bootstrap() {

  const port = process.env.PORT ?? 5080;
  const host = process.env.HOST ?? `localhost`;
  const front_port = process.env.FRONT_PORT ?? 3000;
  const front_host = process.env.FRONT_HOST ?? `localhost`;
  const front_url = `http://${front_host}:${front_port}`;
  const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

  const app = await NestFactory.create<NestFastifyApplication>(AppModule, new FastifyAdapter(), {
    bufferLogs: true,
  });

  app.use(compression());

  // const logger = app.get(WINSTON_MODULE_NEST_PROVIDER);
  // console.log('CORS origin allowed:', front_url);

  app.enableVersioning({
    type: VersioningType.URI,
    defaultVersion: '1',
  });

  app.enableCors({
    origin: [
      front_url,
    ],
    credentials: true,
    methods,
    allowedHeaders: ['Content-Type', 'Authorization'],
  });

  app.use(helmet());
  app.setGlobalPrefix('api');
  app.useGlobalPipes(new ValidationPipe({ whitelist: true, forbidNonWhitelisted: true, transform: true }));
  app.useGlobalFilters(new PrismaExceptionFilter(), new AllExceptionsFilter());
  // app.useLogger(logger);
  // app.use(cookieParser());

  await app.listen(port, host, () => console.log(`\n${host}:${port} - ${(new Date()).toUTCString()}\n ${front_url}`));

  app.enableShutdownHooks();

  setTimeout(() => app.close(), 24 * 60 * 60 * 1000);
}

if (cluster.isPrimary) {
  const numCPUs = os.cpus().length;
  console.log(`Master ${process.pid} is running`);

  for (let i = 0; i < numCPUs; i++) {
    cluster.fork();
  }

  cluster.on('fork', (worker) => {
    console.log('worker is dead:', worker.isDead());
  });

  cluster.on('exit', (worker, code, signal) => {
    console.log(`Worker ${worker.process.pid} died. Restarting...`);
    cluster.fork(); // Replace dead worker
  });

  cluster.fork().on('disconnect', () => {
    console.log('All workers disconnected. Shutting down master process.');
    process.exit(0);
  });

} else {
  console.log(`Worker ${process.pid} started`);
  bootstrap();
}