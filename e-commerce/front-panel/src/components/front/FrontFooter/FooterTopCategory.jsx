import React from 'react'
import { Link } from 'react-router-dom'

function FooterTopCategory() {
    return (
        <>
            <div className={`col-md-4 pt-5`}>
                <h2 className={`h2 text-light border-bottom pb-3 border-light`}>Products</h2>
                <ul className={`list-unstyled text-light footer-link-list`}>
                    <li><Link className={`text-decoration-none`} to={`/`} >Luxury</Link></li>
                    <li><Link className={`text-decoration-none`} to={`/`} >Sport</Link></li>
                    <li><Link className={`text-decoration-none`} to={`/`} >Dress</Link></li>
                    <li><Link className={`text-decoration-none`} to={`/`} >Shoes</Link></li>
                </ul>
            </div>
        </>
    );
}

export default FooterTopCategory