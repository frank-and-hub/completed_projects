import React from 'react'
import ErrorCodes from './components/errors/codes'

export default function NotFoundPage() {
    return (
        <div>
            <ErrorCodes status={404} />
        </div>
    )
}