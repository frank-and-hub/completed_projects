import { btnStyle, radius } from '@/utils/style';
import { Button } from '@mantine/core';
import React from 'react';

interface ButtonProps {
    name?: string;
    className?: string;
    type?: 'button' | 'submit' | 'reset';
    onClick?: () => void;
    disabled?: boolean;
    [x: string]: any;
}

export default function CustomButton({
    name,
    className = '',
    type = 'button',
    onClick,
    disabled = false,
    ...props
}: ButtonProps) {
    return (
        <Button
            className={`${btnStyle} ${className}`}
            variant={`light`}
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
