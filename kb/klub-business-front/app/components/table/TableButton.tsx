import { radius, tableBtn } from '@/utils/style';
import { Button } from '@mantine/core';
import React, { ReactNode } from 'react';

interface ButtonProps {
    name?: string | ReactNode;
    className?: string;
    type?: 'button' | 'submit' | 'reset';
    onClick?: () => void;
    disabled?: boolean;
    variant?: string;
    [x: string]: any;
}

export default function TableButton({
    name,
    className = '',
    type = 'button',
    onClick,
    disabled = false,
    variant = 'light',
    ...props
}: ButtonProps) {
    return (
        <Button
            className={`${tableBtn} ${className} mb-0`}
            variant={variant}
            type={type}
            radius={radius}
            onClick={onClick}
            disabled={disabled}
            {...props}
        >
            {name}
        </Button>
    );
}
