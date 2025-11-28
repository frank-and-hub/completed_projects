import React from 'react';
import { AspectRatio, Avatar, Flex, rem } from '@mantine/core';

interface LogoProps {
    ratio?: number;
}

export default function Logo({ ratio = 1 }: LogoProps) {
    return (
        <Flex align={`center`} justify={`center`} m={`auto`} >
            <AspectRatio ratio={ratio} w={rem(80)}>
                <Avatar
                    src={`/images/avatar/1.png`}
                    alt={`Logo`}
                    radius={rem(ratio * 5)}
                    size={`md`}
                    className={`shadow-lg/10`} 
                />
            </AspectRatio>
        </Flex>
    )
}