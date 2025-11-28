import { sidebarMenu } from '@/components/sideBar/sidebarMenu';
import SideBarItem from '@/components/sideBar/SideBarItem';
import { useIsMobile } from '@/components/hooks/useIsMobile';
import { commonStyle } from '@/utils/style';

interface SidebarProps {
    action: () => void;
    opened: boolean;
    [key: string]: any;
}

export default function SideBar({ action, opened, ...prop }: SidebarProps) {
    const w = 65;
    const isMobile = useIsMobile(720) ?? false;
    const asideStyle = `min-h-fit max-h-full overflow-x-auto text-dark px-5 py-7`;
    const subBarStyle = `text-sm uppercase text-drak font-bold mb-2 dark:text-gray-500`;

    return (
        <aside className={`w-${isMobile ? 'full' : '75'} ${commonStyle} ${asideStyle}`} >
            {sidebarMenu.map((data, index) => (
                <div key={index}>
                    {data.label && <p className={subBarStyle}>{data.label}</p>}
                    <ul className={`space-y-2`}>
                        {data.items.map((item, i) => (
                            <SideBarItem key={i} item={item} weight={w} action={action} opened={opened} />
                        ))}
                    </ul>
                </div>
            ))}
        </aside>
    );
}