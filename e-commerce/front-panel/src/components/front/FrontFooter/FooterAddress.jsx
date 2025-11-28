import React from 'react'
import { Link } from 'react-router-dom'

function FooterAddress() {
    return (
        <>
            <div className={`col-md-4 pt-5`}>
                <h2 className={`h2 text-success border-bottom pb-3 border-light logo`}>Shop</h2>
                <ul className={`list-unstyled text-light footer-link-list`}>
                    <li>
                        <i className={`fas fa-map-marker-alt fa-fw`}></i> 123 Consectetur at ligula 10660
                    </li>
                    <li>
                        <i className={`fa fa-phone fa-fw`}></i>
                        <Link className={`text-decoration-none`} to={`tel:010-020-0340`}>010-020-0340</Link>
                    </li>
                    <li>
                        <i className={`fa fa-envelope fa-fw`}></i>
                        <Link className={`text-decoration-none`} to={`mailto:info@company.com`}>info@company.com</Link>
                    </li>
                </ul>
            </div>
        </>
    );
}

export default FooterAddress