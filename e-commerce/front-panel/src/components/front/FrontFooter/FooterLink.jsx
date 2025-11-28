import React from 'react'
import { Link } from 'react-router-dom'

function FooterLink() {
    return (
        <>
            <div className={`col-auto me-auto`}>
                <ul className={`list-inline text-left footer-icons`}>
                    <li className={`list-inline-item border border-light rounded-circle text-center`}>
                        <Link className={`text-light text-decoration-none`} target={`_blank`} to={`http://facebook.com/`}><i className={`fab fa-facebook-f fa-lg fa-fw`}></i></Link>
                    </li>
                    <li className={`list-inline-item border border-light rounded-circle text-center`}>
                        <Link className={`text-light text-decoration-none`} target={`_blank`} to={`https://www.instagram.com/`}><i className={`fab fa-instagram fa-lg fa-fw`}></i></Link>
                    </li>
                    <li className={`list-inline-item border border-light rounded-circle text-center`}>
                        <Link className={`text-light text-decoration-none`} target={`_blank`} to={`https://twitter.com/`}><i className={`fab fa-twitter fa-lg fa-fw`}></i></Link>
                    </li>
                    <li className={`list-inline-item border border-light rounded-circle text-center`}>
                        <Link className={`text-light text-decoration-none`} target={`_blank`} to={`https://www.linkedin.com/`}><i className={`fab fa-linkedin fa-lg fa-fw`}></i></Link>
                    </li>
                </ul>
            </div>
        </>
    );
}

export default FooterLink