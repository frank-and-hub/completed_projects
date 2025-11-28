import React, { createContext, useState, useContext } from "react";

const DashBoardContext = createContext();

export const useDashboard = () => useContext(DashBoardContext);

export const DashboardProvider = ({ children }) => {
    const [userFilter, setUserFilter] = useState(`today`);
    const [adminFilter, setAdminFilter] = useState(`today`);
    const [guestFilter, setGuestFilter] = useState(`today`);
    const [contactFilter, setContactFilter] = useState(`today`);
    const [paymentFilter, setPaymentFilter] = useState(`today`);
    const [trafficFilter, setTrafficFilter] = useState(`today`);
    const [reportFilter, setReportFilter] = useState(`today`);

    return (
        <DashBoardContext.Provider value={{
            userFilter, setUserFilter,
            adminFilter, setAdminFilter,
            guestFilter, setGuestFilter,
            contactFilter, setContactFilter,
            paymentFilter, setPaymentFilter,
            trafficFilter, setTrafficFilter,
            reportFilter, setReportFilter
        }}>
            {children}
        </DashBoardContext.Provider>
    );
};
