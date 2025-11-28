import React from 'react'
import { Filter } from './Filter'
import ReportsChart from './ReportsChart'
import { useDashboard } from 'context/DashBoardContext'
import { ucwords } from 'utils/helper'

export const ReportGraph = ({ reportDataChart }) => {
    const { reportFilter, setReportFilter } = useDashboard();

    reportDataChart = [
        { name: 'Sales', data: [31, 40, 28, 51, 42, 82, 56] },
        { name: 'Revenue', data: [11, 32, 45, 32, 34, 52, 41] },
        { name: 'Customers', data: [15, 11, 32, 18, 9, 24, 11] }
    ];

    return (
        <>
            <div className={`col-12`}>
                <div className={`card`}>
                    <Filter setFilterFunction={setReportFilter} />
                    <div className={`card-body`}>
                        <h5 className={`card-title`}>Reports <span>| {ucwords(reportFilter)}</span></h5>
                        <ReportsChart reportDataChart={reportDataChart} />
                    </div>
                </div>
            </div>
        </>
    )
};