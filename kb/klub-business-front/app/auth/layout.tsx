import { Box, Flex } from '@mantine/core';

export default function AuthLayout({ children }: { children: React.ReactNode }) {
    return (
        <div className={`space-y-12`}>
            <div className={`border-b border-gray-900/10 pb-12`}>
                <Flex className="min-h-screen overflow-auto items-center bg-transparent justify-center px-4">
                    <div className="bg-transparent w-full max-w-md md:p-6 xl:p-4 sm:p-8 lg:p-2 shadow-2xl rounded-3xl">
                        <Box maw={500} mx={`auto`} my={`md`} px={'md'} >
                            {children}
                        </Box>
                    </div>
                </Flex>
            </div>
        </div>
    );
}
