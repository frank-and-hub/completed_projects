import React, { useContext } from 'react'
import SidebarItem from './SidebarItem'
import { SidebarContext } from 'context/SidebarContext'
// import { Loading } from '../loading/Loading'
import SidebarItemSkeleton from '../form/skeleton/SidebarItemSkeleton'

const SideBar = () => {
    const { menus, loading } = useContext(SidebarContext);
    return (
        <>
            <aside id={`sidebar`} className={`sidebar shadow my-3 mt-4 mx-2 rounded-50 d-md-block`} style={{ scrollbarWidth: "none" }}>
                <ul className={`sidebar-nav`} id={`sidebar-nav`}>
                    {loading ? (
                        <SidebarItemSkeleton length={menus?.count} />
                    ) : menus && menus?.count > 0 ? (
                        menus?.data.map((item, index) => (
                            <SidebarItem
                                key={index}
                                route={item?.route}
                                icon={item?.icon}
                                label={item?.name}
                                subItems={item?.sub_menu}
                            />
                        ))
                    ) : (
                        <li>No data available...</li>
                    )}
                </ul>
            </aside>
        </>
    )
}

export default SideBar