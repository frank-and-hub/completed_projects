import React from 'react'
import { Link } from 'react-router-dom'

function HeaderNavBar() {

    const handelLogOutClick = (e) => {
        console.log();
    }

    const navBar = [
        {
            key: 'about',
            val: 'About',
        },
        {
            key: 'shops',
            val: 'Shop',
        },
        {
            key: 'contact',
            val: 'Contact',
        }
    ]; 

    return (
        <>
            <div className={`flex-fill`}>
                <ul className={`nav navbar-nav d-flex justify-content-between mx-lg-auto`}>
                    {navBar.map((value, index) => (
                        <li className={`nav-item`} key={index} >
                            <Link className={`nav-link`} to={value.key} >{value.val}</Link>
                        </li>
                    ))}
                    {true ? (
                        <>
                            <li className={`nav-item`}>
                                <Link className={`nav-link`} to={`#`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_login`} >Login</Link>
                            </li>
                            <li className={`nav-item`}>
                                <Link className={`nav-link`} to={`#`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_register`} >Register</Link>
                            </li>
                        </>
                    ) : (
                        <li className={`nav-item`} >
                            <Link className={`nav-link`} to={`#`} onClick={handelLogOutClick} >Logout</Link>
                        </li>
                    )}
                </ul>
            </div>
        </>
    );
}

export default HeaderNavBar