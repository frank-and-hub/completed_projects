import React from 'react'
import {Filter} from '../Filter'

function BudgetReport() {
    return (
        <>
            <div className={`card`}>
                <Filter />
                <div className={`card-body pb-0`}>
                    <h5 className={`card-title`}>Budget Report <span>| This Month</span></h5>
                    <div id="budgetChart" style={{ minHeight: "400px" }} className={`echart`}></div>
                </div>
            </div>
        </>
    )
}

export default BudgetReport