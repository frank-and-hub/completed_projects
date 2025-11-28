import { Anchor, Avatar, Box, Group, Menu, rem } from "@mantine/core";
import Logo from "../logo/Logo";
import Link from "next/link";
import { ArrowLeftOnRectangleIcon, ArrowRightOnRectangleIcon, UserCircleIcon, UserPlusIcon } from "@heroicons/react/24/outline";
import { logout } from "@/utils/useAuth";
import { useEffect } from "react";
import { headerStyle, radius } from "@/utils/style";
import { cl } from "@/utils/console";

interface HeaderProps {
    opened: boolean;
    toggle: () => void;
    [key: string]: any;
}

export default function Header({ opened, toggle, ...prop }: HeaderProps) {

    const iconsClassName = `w-5 h-5`;
   

    const handleLogout = () => {
        if (!confirm('Logged out!')) return;
        logout();
        window.location.href == `/auth/sign-in`;
    }

    useEffect(() => {
        const handleScroll = () => {
            cl('scrolling');
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <Group justify={`space-between`} gap={`xs`} py={{ base: `md`, sm: `xs`, md: `md`, lg: `md` }} px={`md`} className={headerStyle} {...prop}  style={{ backdropFilter: 'blur(5px)' }}>
            <Anchor underline={`never`} onClick={toggle} className={`cursor-pointer py-0.5`}>
                <Box ml={rem(5)}>
                    <Logo ratio={2} />
                </Box>
            </Anchor>

            <Menu shadow={`md`} width={120} position={`bottom-end`} trigger={`hover`} openDelay={100} closeDelay={400} >
                <Menu.Target >
                    <Group gap={rem(6)} className={`cursor-pointer`} >
                        <Avatar src={`/images/avatar/2.png`} alt="User" radius={radius} size={`md`} title={`User Name`} className={`shadow-lg/10`} />
                    </Group>
                </Menu.Target>

                <Menu.Dropdown>
                    <Menu.Item component={Link} href={`/auth/sign-in`} title={`Login page`} leftSection={<ArrowLeftOnRectangleIcon className={iconsClassName} />}>Login</Menu.Item>
                    <Menu.Item component={Link} href={`/auth/sign-up`} title={`Register page`} leftSection={<UserPlusIcon className={iconsClassName} />}>Register</Menu.Item>
                    <Menu.Item component={Link} href={`/admin/profile`} title={`My profile page`} leftSection={<UserCircleIcon className={iconsClassName} />}>Profile</Menu.Item>
                    <Menu.Item color={`purple`} title={`Logout`} onClick={handleLogout} leftSection={<ArrowRightOnRectangleIcon className={iconsClassName} />}>Logout</Menu.Item>
                </Menu.Dropdown>
            </Menu>
        </Group>
    );
}
