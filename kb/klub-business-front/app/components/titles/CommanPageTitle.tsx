'use client';

import { useAuth } from '@/context/AuthContext';
import { Box, Center, Title } from '@mantine/core';
import { usePathname } from 'next/navigation';
import { filterSegments } from '../breadcrumbs/Breadcrumb';

export default function CommanPageTitle() {
    const pathname = usePathname();
    const segments = filterSegments(pathname);
    const { pagetitle } = useAuth();
    const lastSegment = segments[segments.length - 1] || 'Dashboard';

    const formatTitle = (slug: string) => slug.replace(/[-_]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());

    return (
        <Box mb={0} className={`mb-5 sm:mb-3`}>
            <Center >
                <Title order={2} className={`px-5`}>
                    {formatTitle(pagetitle
                        ? pagetitle.length > 0
                            ? pagetitle
                            : lastSegment
                        : lastSegment)}
                </Title>
            </Center>
        </Box>
    );
}