import React, { useEffect } from 'react'

import { UsersCard } from './UsersCard'
import { GuestsCard } from './GuestsCard'
import { AdminsCard } from './AdminsCard'
import { ContactCard } from './ContactCard'
import { PaymentCard } from './PaymentCard'
import { ReportGraph } from './ReportGraph'
import { TrafficCard } from './TrafficCard'

import { useDashboard } from 'context/DashBoardContext'
import { post } from 'utils/AxiosUtils'
import { useState } from 'react'

function Dashboard() {
    const { userFilter, adminFilter, guestFilter, contactFilter, paymentFilter, trafficFilter, reportFilter } = useDashboard();
    const [response, setResponse] = useState({});

    useEffect(() => {

        const fetchData = async () => {
            try {
                const dataFilter = {
                    filter: {
                        userFilter,
                        adminFilter,
                        guestFilter,
                        contactFilter,
                        paymentFilter,
                        trafficFilter,
                        reportFilter
                    }
                };

                const [res] = await Promise.all([
                    post(`/dashboard`, dataFilter),
                ]);
                setResponse(res?.response?.data);
            } catch (err) {
                console.error(err.message);
            }
        };
        fetchData();

    }, [userFilter, adminFilter, guestFilter, contactFilter, paymentFilter, trafficFilter, reportFilter]);

    return (
        <>
            <section className={`section dashboard mt-0`}>
                <div className={`row`}>
                    <div className={`col-lg-8 col-12`}>
                        <div className={`row`}>
                            <UsersCard usersCount={response?.usersCount} />
                            <GuestsCard guestsCount={response?.guestsCount} />
                            <AdminsCard adminsCount={response?.adminsCount} />
                            <ContactCard contactUsCount={response?.contactUsCount} />
                            <PaymentCard paymentAmount={response?.paymentAmount} />
                        </div>
                    </div>
                    <div className={`col-lg-4 col-12`}>
                        <TrafficCard trafficDataChart={response?.trafficDataChart} />
                    </div>
                    <div className={`col-12`}>
                    <ReportGraph reportDataChart={response?.reportDataChart} />
                </div>
            </div>
        </section >
        </>
    )
}

export default Dashboard