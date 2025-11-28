import {
    ExceptionFilter,
    Catch,
    ArgumentsHost,
    HttpException,
    HttpStatus,
} from '@nestjs/common';
import { Response } from 'express';
import { Prisma } from '@prisma/client';

@Catch(Prisma.PrismaClientKnownRequestError)
export class PrismaExceptionFilter implements ExceptionFilter {
    catch(exception: Prisma.PrismaClientKnownRequestError, host: ArgumentsHost) {
        const ctx = host.switchToHttp();
        const response = ctx.getResponse<Response>();

        let statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
        let message = 'Internal server error';

        // Customize error based on Prisma error code
        switch (exception.code) {
            case 'P2000': // 413
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Input value is too long for the field: ${exception.meta?.target}`;
                break;

            case 'P2001': // 404
                statusCode = HttpStatus.NOT_FOUND;
                message = `Record not found for the specified filter: ${exception.meta?.target}`;
                break;

            case 'P2002': // 409
                statusCode = HttpStatus.CONFLICT;
                message = `Unique constraint failed on field(s): ${exception.meta?.target}`;
                break;

            case 'P2003': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Foreign key constraint failed on the field: ${exception.meta?.field_name}`;
                break;

            case 'P2004': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `A query failed to execute: ${exception.meta?.reason}`;
                break;

            case 'P2005': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `A constraint failed on the database: ${exception.meta?.database_error}`;
                break;

            case 'P2005': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `A constraint failed on the database: ${exception.meta?.database_error}`;
                break;

            case 'P2006': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `The provided value for ${exception.meta?.model_name} is not valid`;
                break;

            case 'P2007': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Data validation error`;
                break;

            case 'P2008': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `Failed to parse the query. This is likely a bug in Prisma.`;
                break;

            case 'P2009': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `Query validation failed. This is likely a bug in Prisma.`;
                break;

            case 'P2010': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `Raw query failed. Check your query syntax and parameters.`;
                break;

            case 'P2011': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Null constraint violation on field: ${exception.meta?.constraint}`;
                break;

            case 'P2012': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Missing required value for field: ${exception.meta?.field_name}`;
                break;

            case 'P2013':
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Missing required argument for query`;
                break;

            case 'P2014': // 409
                statusCode = HttpStatus.CONFLICT;
                message = `The change violates a relation constraint between two records.`;
                break;

            case 'P2015': // 404
                statusCode = HttpStatus.NOT_FOUND;
                message = `Record for relation not found.`;
                break;

            case 'P2016': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `Query interpretation error.`;
                break;

            case 'P2017': // 409
                statusCode = HttpStatus.CONFLICT;
                message = `The records are related in a way that would cause a cycle.`;
                break;

            case 'P2018': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `The required connected records were not found.`;
                break;

            case 'P2025': // 404
                statusCode = HttpStatus.NOT_FOUND;
                message = `Record not found: ${exception.meta?.cause}`;
                break;

            case 'P2026': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `The current database provider doesn't support a feature you used.`;
                break;

            case 'P2027': // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = `Multiple errors occurred on the database during query execution.`;
                break;

            case 'P2030': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `Cannot find a fulltext index to use for the search.`;
                break;

            case 'P2033': // 400
                statusCode = HttpStatus.BAD_REQUEST;
                message = `A number used in the query does not fit into the specified type.`;
                break;

            default: // 500
                statusCode = HttpStatus.INTERNAL_SERVER_ERROR;
                message = exception.message || 'Unexpected Prisma error occurred.';
                break;
        }

        if (process.env.NODE_ENV !== 'production') {
            console.error('Prisma Error:', exception);
        }

        response.status(statusCode).json({
            statusCode,
            message,
            error: 'PrismaClientKnownRequestError',
        });
    }
}

@Catch()
export class AllExceptionsFilter implements ExceptionFilter {
    catch(exception: unknown, host: ArgumentsHost) {
        const ctx = host.switchToHttp();
        const response = ctx.getResponse<Response>();
        const request = ctx.getRequest<Request>();
        const status = exception instanceof HttpException ? exception.getStatus() : HttpStatus.INTERNAL_SERVER_ERROR;

        response.status(status).json({
            statusCode: status,
            timestamp: new Date().toISOString(),
            path: request.url,
            message: exception instanceof HttpException ? exception.getResponse() : 'Internal server error',
        });
    }
}

