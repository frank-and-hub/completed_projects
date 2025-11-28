import React, { useState, useEffect, useContext } from 'react'
import { useNavigate } from 'react-router-dom'
import SubSidebarItem from './SubSidebarItem'
import { ucwords } from 'utils/helper'
import { SidebarContext } from 'context/SidebarContext'

const SidebarItem = ({ icon, label, route, subItems }) => {
    const [active, setActive] = useState(null);
    const { pathname } = useContext(SidebarContext);
    const navigate = useNavigate();

    useEffect(() => {
        const activeMenu = localStorage.getItem('active-child-menu');
        setActive(activeMenu);
    }, [active, subItems, pathname]);

    const handleSetActive = (e) => {
        localStorage.setItem('active-child-menu', e);
    };

    return (
        <li className={`nav-item`}>
            {subItems ? (
                <SubSidebarItem
                    icon={icon}
                    label={label}
                    subItems={subItems}
                    route={route} />
            ) : (
                <button
                    className={`nav-link rounded-pill col-12 text-capitalize ${active === label ? 'active' : 'collapsed'}`}
                    data-toggle={`tooltip`}
                    data-placement={`top`}
                    title={ucwords(label)}
                    onClick={(e) => {
                        e.preventDefault();
                        navigate(route, { replace: true });
                        handleSetActive(label);
                    }}
                >
                    <i className={icon}></i> {(label)}
                </button>
            )}
        </li>
    );
}

export default SidebarItem