import React from 'react'
import NevBarLinks from './NevBarLinks';
import { Link } from 'react-router-dom'

function Navbar() {
    return (
        <>
            <nav className={`navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block`} id={`templatemo_nav_top`}>
                <div className={`container text-light`}>
                    <div className={`w-100 d-flex justify-content-between`}>
                        <div className={``}>
                            <i className={`fa fa-envelope mx-2`}></i>
                            <Link className={`navbar-sm-brand text-light text-decoration-none`} to={`mailto:info@company.com`}>info@company.com</Link>
                            <i className={`fa fa-phone mx-2`}></i>
                            <Link className={`navbar-sm-brand text-light text-decoration-none`} to={`tel:010-020-0340`}>010-020-0340</Link>
                        </div>
                        <NevBarLinks />
                    </div>
                </div>
            </nav>
        </>
    );
}

export default Navbar