import React from 'react'
import { Link } from 'react-router-dom'

export default function Messages() {
    return (
        <>
            <li className={`nav-item dropdown`}>
                <Link to={`#`} className={`nav-link nav-icon`} onClick={(e) => e.preventDefault()} data-bs-toggle={`dropdown`}>
                    <i className={`bi bi-chat-left-text`}></i>
                    <span className={`badge bg-success badge-number`}>3</span>
                </Link>
                <ul className={`dropdown-menu dropdown-menu-end dropdown-menu-arrow messages rounded-50 card-color p-3`} style={{ background: `var(--white)` }}>
                    <li className={`dropdown-header`}>
                        You have 3 new messages
                        <Link to={`#`} onClick={(e) => e.preventDefault()}><span className={`badge rounded-pill bg-primary p-2 ms-2`}>View all</span></Link>
                    </li>
                    <li className={`dropdown-footer`}>
                        <Link to={`#`} onClick={(e) => e.preventDefault()}>Show all messages</Link>
                    </li>
                </ul>
            </li>
        </>
    )
}
