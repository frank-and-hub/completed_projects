import React from 'react'
import { Filter } from './Filter'
import { ucwords } from 'utils/helper'
import { useDashboard } from 'context/DashBoardContext'

export const GuestsCard = ({ guestsCount }) => {
    const { guestFilter, setGuestFilter } = useDashboard();

    return (
        <>
            <div className={`col-xxl-4 col-md-6`}>
                <div className={`card info-card`}>
                    <Filter setFilterFunction={setGuestFilter} />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Guests <span>| {ucwords(guestFilter)}</span></h5>
                        <div className={`d-flex align-items-center`}>
                            <div className={`card-icon rounded-circle d-flex align-items-center justify-content-center`}>
                                <i className={`bi bi-people`}></i>
                            </div>
                            <div className={`ps-3`}>
                                <h6>{guestsCount}</h6>
                                <span className={`text-success small pt-1 fw-bold`}></span> <span className={`text-muted small pt-2 ps-1`}></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </>
    )
};