import React from 'react'
import { Filter } from './Filter'
import { ucwords } from 'utils/helper'
import { useDashboard } from 'context/DashBoardContext'

export const PaymentCard = ({ paymentAmount }) => {
    const { paymentFilter, setPaymentFilter } = useDashboard();
    return (
        <>
            <div className={`col-xxl-8 col-xl-12`}>
                <div className={`card info-card customers-card`}>
                    <Filter setFilterFunction={setPaymentFilter} />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Payment <span>| {ucwords(paymentFilter)}</span></h5>

                        <div className={`d-flex align-items-center`}>
                            <div className={`card-icon rounded-circle d-flex align-items-center justify-content-center`}>
                                <i className={`bi bi-currency-dollar`}></i>
                            </div>
                            <div className={`ps-3`}>
                                <h6>{paymentAmount}</h6>
                                <span className={`text-danger small pt-1 fw-bold`}></span> <span className={`text-muted small pt-2 ps-1`}></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
};