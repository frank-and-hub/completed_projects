import React from 'react'
import { Link } from 'react-router-dom'
import { handleToggleSidebar } from 'utils/helper'

function Logo() {
    return (
        <>
            <div className={`d-flex align-items-center justify-content-between`} >
                <Link
                    className={`logo d-flex align-items-center`}
                    onClick={handleToggleSidebar}
                >
                    <img className={`d-block d-md-none`} src={`/admin/img/logo.svg`} alt={`logo`} width={`26px`} height={`26px`} />
                    <span className={`d-none d-md-block`} >Admin</span>
                </Link>
            </div>
        </>
    )
}

export default Logo