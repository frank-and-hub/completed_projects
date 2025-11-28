'use client';

import { Breadcrumbs, Anchor, Box, Flex } from '@mantine/core';
import { usePathname } from 'next/navigation';
import Link from 'next/link';
import { HomeIcon } from '@heroicons/react/24/outline';
import { breadcrumbsStyle } from '@/utils/style';

export const filterList = [
    'list'
];

export const filterSegments = (pathname: string) => {
    return pathname.split('/').filter(Boolean).filter((segment) => /^[a-zA-Z0-9]+$/.test(segment)).filter((segment) => !filterList.includes(segment));
}

export default function PageBreadcrumbs() {
    const pathname = usePathname();
    const segments = filterSegments(pathname);
    const items = segments.map((segment, index) => {
        const href = '/' + segments.slice(0, index + 1).join('/');
        const label = decodeURIComponent(segment).replace(/-/g, ' ');
        return (
            <Anchor component={Link} href={href} key={index} underline={`never`} c={`dark`} >
                {label.charAt(0).toUpperCase() + label.slice(1)}
            </Anchor>
        );
    });

    const breadcrumbs = [
        <Anchor component={Link} href={`/`} key={`home`}>
            <HomeIcon className={`w-5 h-5`} />
        </Anchor>,
        ...items,
    ];

    return (
        <Box mb={0} className={`mb-5 sm:mb-3`}>
            <Flex justify={`flex-end`} >
                <Breadcrumbs className={breadcrumbsStyle} separatorMargin={`md`} mt={`xs`} style={{ backdropFilter: 'blur(10px)' }}>
                    {breadcrumbs}
                </Breadcrumbs>
            </Flex >
        </Box>
    );
}
