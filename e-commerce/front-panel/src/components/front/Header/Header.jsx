import React from 'react'
import { Link } from 'react-router-dom'
import HeaderNavBar from './HeaderNavBar';
import HeaderCart from './HeaderCart';
import HeaderButton from './HeaderButton';

function Header() {
    return (
        <>
            <nav className={`navbar navbar-expand-lg navbar-light shadow`}>
                <div className={`container d-flex justify-content-between align-items-center`}>

                    <Link className={`navbar-brand text-success logo h1 align-self-center`} to={`index`} >
                        eCom
                    </Link>

                    <button className={`navbar-toggler border-0`} type={`button`} data-bs-toggle={`collapse`} data-bs-target={`#templatemo_main_nav`} aria-controls={`navbarSupportedContent`} aria-expanded={false} aria-label={`Toggle navigation`}>
                        <span className={`navbar-toggler-icon`}></span>
                    </button>

                    <div className={`align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between`} id={`templatemo_main_nav`}>
                        <HeaderNavBar />
                        <div className={`navbar align-self-center d-flex`}>
                            <HeaderButton />
                            <Link className={`nav-icon d-none d-lg-inline`} to={`#`} title={`Search`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_search`}>
                                <i className={`fa fa-fw fa-search text-dark mr-2`}></i>
                            </Link>
                            <HeaderCart />
                            <Link className={`nav-icon position-relative text-decoration-none`} title={`Login`} to={`#`}>
                                <i className={`fa fa-fw fa-user text-dark mr-3`}></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>
        </>
    );
}

export default Header