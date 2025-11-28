import React from 'react'
import Designedby from './designedby/Designedby'
import { Link } from 'react-router-dom'

function Footer() {
    return (
        <>
            <footer id={`footer`} className={`pb-3 px-2 fixed-bottom`} >
                <div className={`shadow rounded-50 mx-2`} style={{ background: 'var(--background)' }}>
                    <div className={`copyright pt-2 pb-1`}>
                        Copyright &copy; {new Date().getFullYear()} <strong><span>Admin</span></strong>. All Rights Reserved
                    </div>
                    <Designedby />
                </div>
            </footer>

            <Link to={`#`} onClick={(e) => e.preventDefault()} className={`back-to-top d-flex align-items-center justify-content-center rounded-50`} >
                <i className={`bi bi-arrow-up-short`}></i>
            </Link>
        </>
    )
}

export default Footer