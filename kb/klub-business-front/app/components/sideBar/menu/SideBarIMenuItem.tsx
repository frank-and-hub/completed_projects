'use client';
import { Group, Menu, rem } from '@mantine/core';

type MenuLink = {
  label: string;
  href?: string;
  icon?: string;
  children?: MenuLink[];
};

export function SideBarIMenuItem({ item }: { item: MenuLink }) {
  const hasChildren = item.children && item.children.length > 0;

  if (hasChildren) {
    return (
      <Menu
        shadow="md"
        width={260}
        position="bottom-end"
        trigger="hover"
        openDelay={100}
        closeDelay={400}
      >
        <Menu.Target>
          <Group gap={rem(7)} className="cursor-pointer">
            {item.label}
          </Group>
        </Menu.Target>

        <Menu.Dropdown>
          {item.children!.map((child, idx) => (
            <SideBarIMenuItem key={idx} item={child} />
          ))}
        </Menu.Dropdown>
      </Menu>
    );
  }

  return <Menu.Item>{item.label}</Menu.Item>;
}
