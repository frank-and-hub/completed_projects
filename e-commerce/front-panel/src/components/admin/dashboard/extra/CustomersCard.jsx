import React from 'react'
import {Filter} from '../Filter'

function CustomersCard() {
    return (
        <>
            <div className={`col-xxl-4 col-xl-12`}>
                <div className={`card info-card customers-card`}>
                    <Filter />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Customers <span>| This Year</span></h5>

                        <div className={`d-flex align-items-center`}>
                            <div className={`card-icon rounded-circle d-flex align-items-center justify-content-center`}>
                                <i className={`bi bi-people`}></i>
                            </div>
                            <div className={`ps-3`}>
                                <h6>1244</h6>
                                <span className={`text-danger small pt-1 fw-bold`}>12%</span> <span className={`text-muted small pt-2 ps-1`}>decrease</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

export default CustomersCard