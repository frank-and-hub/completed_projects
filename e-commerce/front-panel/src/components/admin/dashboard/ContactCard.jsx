import React from 'react'
import { Filter } from './Filter'
import { useDashboard } from 'context/DashBoardContext'
import { ucwords } from 'utils/helper'

export const ContactCard = ({ contactUsCount }) => {
    const { contactFilter, setContactFilter } = useDashboard();
    return (
        <>
            <div className={`col-xxl-4 col-md-6`}>
                <div className={`card info-card revenue-card`}>
                    <Filter setFilterFunction={setContactFilter} />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Contact Us<span> | {ucwords(contactFilter)}</span></h5>
                        <div className={`d-flex align-items-center`}>
                            <div className={`card-icon rounded-circle d-flex align-items-center justify-content-center`}>
                                <i className={`bi bi-phone`}></i>
                            </div>
                            <div className={`ps-3`}>
                                <h6>{contactUsCount}</h6>
                                <span className={`text-success small pt-1 fw-bold`}></span> <span className={`text-muted small pt-2 ps-1`}></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
};