import Link from "next/link";
import { useState } from "react";
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/react/24/outline';
import { cap } from "@/utils/helpers";
import { rem } from "@mantine/core";

type MenuLink = {
  label: string;
  href?: string;
  icon?: string;
  children?: MenuLink[];
};

export default function SideBarItem({ weight, item, action, opened }: { weight: number, item: MenuLink, action: () => void, opened: boolean }) {
  const [open, setOpen] = useState(false);
  const hasChildren = item.children && item.children.length > 0;

  const toggle = () => {
    setOpen((prev) => !prev);
  };

  const iconClass = `w-3 h-3 text-red border-none`;
  const testStyle = `flex items-center gap-2 justify-between px-2 py-2 mb-2 text-sm rounded-2xl text-gray-800 dark:text-gray-100 dark:bg-gray-100 dark:border-gray-700 transition-all duration-500 ease-in-out hover:shadow-sm/10 hover:scale-[0.99]`;
  const classStyle = `w-${weight - 8} ${testStyle}`;

  return (
    <li>
      {hasChildren ? (
        <>
          <div onClick={toggle} className={classStyle}>
            <span className="flex items-center gap-2">
              {item.icon && <i className={`${item.icon} w-4`} />}
              {cap(item.label)}
            </span>
            {open ? <ChevronUpIcon className={iconClass} /> : <ChevronDownIcon className={iconClass} />}
          </div>
          {open && (
            <ul className="ml-4 mt-1 space-y-1 pl-4">
              {item.children!.map((child, index) => (
                <SideBarItem item={child} key={index} weight={weight - 5} action={action} opened={opened} />
              ))}
            </ul>
          )}
        </>
      ) : (
        <Link
          href={item.href || '#'}
          className={classStyle}
          onClick={action}
          style={{ backdropFilter: `blur(${rem(5)})` }}
        >
          {item.icon && <i className={`${item.icon} w-4`} />}
          {cap(item.label)}
        </Link>
      )}
    </li>
  );
}
