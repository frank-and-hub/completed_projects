import React from 'react'
import { Filter } from './Filter'
import { TrafficChart } from './TrafficChart'
import { useDashboard } from 'context/DashBoardContext'
import { ucwords } from 'utils/helper'

export const TrafficCard = ({ trafficDataChart }) => {
    const { trafficFilter, setTrafficFilter } = useDashboard();

    return (
        <>
            <div className={`card`}>
                <Filter setFilterFunction={setTrafficFilter} />
                <div className={`card-body pb-0`}>
                    <h5 className={`card-title`}> Traffic <span>| {ucwords(trafficFilter)}</span></h5>
                    <TrafficChart trafficDataChart={trafficDataChart} />
                </div>
            </div>
        </>
    )
};