'use client';
import React from 'react'

type ErrorCode = 400 | 401 | 403 | 404 | 405 | 408 | 409 | 429 | 500 | 501;

const codes: Record<ErrorCode, { title: string; message: string }> = {
    400: { title: `Bad Request`, message: `The server cannot process the request due to malformed syntax or invalid parameters.` },
    401: { title: `Unauthorized`, message: `Authentication is required, but the client has not provided valid credentials.` },
    403: { title: `Forbidden Access`, message: `The server understands the request but refuses to authorize it, usually due to insufficient permissions.` },
    404: { title: `Page Not Found`, message: `The requested resource could not be found on the server.` },
    405: { title: `Method Not Allowed`, message: `The HTTP method used in the request is not supported for the requested resource.` },
    408: { title: `Request Timeout`, message: `The server timed out waiting for the client to send a complete request.` },
    409: { title: `Conflict`, message: `The request could not be completed due to a conflict with the current state of the resource.` },
    429: { title: `Too Many Requests`, message: `The client has sent too many requests in a given amount of time (rate limiting).` },
    500: { title: `Internal Server Error`, message: `The server encountered an unexpected condition that prevented it from fulfilling the request.` },
    501: { title: `Not Implemented`, message: `The server does not support the functionality required to fulfill the request.` },
};

export default function ErrorCodes({ status = 404 }: { status?: ErrorCode }) {
    return (
        <div className={`flex flex-col items-center justify-center min-h-screen text-center p-10`}>
            <h1 className={`text-4xl font-bold text-red-600`}>{status} - {codes[status]?.title}</h1>
            <p className={`mt-4 text-gray-600 dark:text-gray-300`}>
                Sorry, {codes[status]?.message}.
            </p>
        </div>
    )
}