import React, { useEffect, useState } from 'react'
// import { get } from 'utils/AxiosUtils'

import Navbar from '../Navbar/Navbar'
import Header from '../Header/Header'
import Model from '../Modal/Modal'
import FrontFooter from '../FrontFooter/FrontFooter'
import { Outlet } from 'react-router-dom'

function FrontLayout() {
    const [loading, setLoading] = useState(null);

    useEffect(() => {
        setLoading(true)
        const fetchData = async () => {
            try {
                setLoading(false)
            } catch (err) {
                setLoading(true)
                console.error(err);
            }
        };

        fetchData();
    }, []);

    return (
        <>
            {loading ? (
                <>
                </>
            ) : (
                <>
                    <Navbar />
                    <Header />
                    <Model />
                    <>
                        <Outlet />
                    </>
                    <FrontFooter />
                </>
            )}
        </>
    );
}

export default FrontLayout