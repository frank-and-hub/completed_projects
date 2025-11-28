import React, { useContext, useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SidebarItem from './SidebarItem'
import { ucwords } from 'utils/helper'
import { SidebarContext } from 'context/SidebarContext';

function SubSidebarItem({ icon, label, subItems, route = null }) {
    const navigate = useNavigate();
    const { pathname } = useContext(SidebarContext);
    const [active, setActive] = useState(null);
    const id = `${label.replace(/\s+/g, '').toLowerCase()}-nav`;

    useEffect(() => {
        
        const activeMenu = localStorage.getItem('active-main-menu');
        setActive(activeMenu);
    }, [active, subItems, pathname]);

    const handleSetActive = (e) => {
        localStorage.setItem('active-main-menu', e);
    };

    return (
        <>
            <Link
                className={`nav-link rounded-pill text-capitalize ${active === label ? 'active' : 'collapsed'}`}
                data-bs-target={`#${id}`}
                data-bs-toggle={`collapse`}
                role={`button`}
                aria-expanded={false}
                aria-controls={id}
                // to={`${route ?? null}`}
                onClick={(e) => {
                    e.preventDefault();
                    navigate(route, { replace: route ? true : false });
                    handleSetActive(label);
                }}
                data-toggle={`tooltip`}
                data-placement={`top`}
                title={ucwords(label)} >
                <i className={`${icon}`}></i> {(label)} {subItems && subItems.length > 0 && <i className={`font-weight-bold bi bi-chevron-down ms-auto`}></i>}
            </Link>
            {subItems && subItems.length > 0 && (
                <ul
                    id={id}
                    className={`nav-content ${active === label ? '' : 'collapse'} `}
                    data-bs-parent={`#sidebar-nav`} >
                    {subItems.map((subItem, index) => (
                        <SidebarItem
                            key={index}
                            label={subItem.name}
                            route={subItem.route} />
                    ))}
                </ul>
            )}
        </>
    );
}

export default SubSidebarItem;
