import React from 'react'
import { Link } from 'react-router-dom'

function HeaderCart() {
    return (
        <>
            <Link className={`nav-icon position-relative text-decoration-none`} to={`#`} title={`Cart`}  >
                <i className={`fa fa-fw fa-cart-arrow-down text-dark mr-1`}></i>
                <span className={`position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark`}>7</span>
            </Link>
        </>
    );
}

export default HeaderCart