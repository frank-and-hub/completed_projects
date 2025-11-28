import Link from 'next/link';
import { SideBarIMenuItem } from '@/components/sideBar/menu/SideBarIMenuItem';
import { sidebarMenu } from '@/components/sideBar/sidebarMenu';
import { Group, Menu, rem } from '@mantine/core';

export default function MenuBar() {
  return (
    <aside className="w-full sm:w-72 h-screen p-4 bg-white shadow-xl">
      {sidebarMenu.map((data, index) => {
        return (
          <Menu key={index} shadow={`md`} width={120} position="bottom-end" trigger="hover" openDelay={100} closeDelay={400} >
            <Menu.Target >
              <Group gap={rem(6)} className={`cursor-pointer`} >
                {data.label}
              </Group>
            </Menu.Target>
            <Menu.Dropdown>
              {data.items.map((item, i) => (
                <SideBarIMenuItem item={item} />
              ))}
            </Menu.Dropdown>
          </Menu>
        )
      })}
    </aside>
  );
}
