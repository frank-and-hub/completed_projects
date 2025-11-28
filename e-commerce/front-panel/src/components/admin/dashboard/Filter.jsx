import React from 'react'
import { Link } from 'react-router-dom'

export const Filter = ({ setFilterFunction }) => {
    return (
        <>
            <div className={`filter`}>
                <Link className={`icon`} onClick={(e) => e.preventDefault()} data-bs-toggle={`dropdown`}>
                    <i className={`bi bi-three-dots`}></i>
                </Link>
                <ul className={`dropdown-menu dropdown-menu-end dropdown-menu-arrow rounded-50`}>
                    <li className={`dropdown-header text-start`}>
                        <h6>Filter</h6>
                    </li>
                    <li>
                        <Link to={`#`} className={`dropdown-item`} onClick={(e) => setFilterFunction(`today`)}>Today</Link>
                    </li>
                    <li>
                        <Link to={`#`} className={`dropdown-item`} onClick={(e) => setFilterFunction(`this_week`)}>This Week</Link>
                    </li>
                    <li>
                        <Link to={`#`} className={`dropdown-item`} onClick={(e) => setFilterFunction(`this_month`)}>This Month</Link>
                    </li>
                    <li>
                        <Link to={`#`} className={`dropdown-item`} onClick={(e) => setFilterFunction(`this_year`)}>This Year</Link>
                    </li>
                </ul>
            </div>
        </>
    )
};