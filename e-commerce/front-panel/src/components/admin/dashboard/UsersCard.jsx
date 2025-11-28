import React from 'react'
import { Filter } from './Filter'
import { useDashboard } from 'context/DashBoardContext'
import { ucwords } from 'utils/helper'

export const UsersCard = ({ usersCount }) => {
    const { userFilter, setUserFilter } = useDashboard();
    return (
        <>
            <div className={`col-xxl-4 col-md-6`}>
                <div className={`card info-card`}>
                    <Filter setFilterFunction={setUserFilter} />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Users <span>| {ucwords(userFilter)}</span></h5>
                        <div className={`d-flex align-items-center`}>
                            <div className={`card-icon rounded-circle d-flex align-items-center justify-content-center`}>
                                <i className={`bi bi-person`}></i>
                            </div>
                            <div className={`ps-3`}>
                                <h6>{usersCount}</h6>
                                <span className={`text-success small pt-1 fw-bold`}></span> <span className={`text-muted small pt-2 ps-1`}></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </>
    )
};