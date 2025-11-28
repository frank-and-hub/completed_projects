import React, { useEffect, useState } from 'react'
import { useDispatch } from 'react-redux'
import { get } from 'utils/AxiosUtils'
import { processNotifications } from 'utils/notificationUtils'
import Chat from '../Chat'
import { useNavigate } from 'react-router-dom'

const Support = () => {

    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [supportDetails, setSupportDetails] = useState({});

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [supportData] = await Promise.all([
                    get(`/support-details`),
                ]);

                setSupportDetails(supportData?.response?.data || {});
                processNotifications(200, supportData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        fetchData();
    }, [dispatch]);

    return (
        <>
            <section className={`section contact`}>
                <div className={`card-head`}>
                    <div className={`card-title`}>
                        <p className={`py-0 m-0 btn`}  onClick={() => (navigate('/admin/supports/create', true))}>Support </p>
                    </div>
                </div>
                <div className={`row gy-4`}>
                    <div className={`${supportDetails.length > 0 ? `col-xl-8` : `col-md-8 col-12 m-0`}`}>
                        {supportDetails && supportDetails.length > 0 && supportDetails.map((values) => (
                            <div className={`p-0`}>
                                <div class="row">
                                    <div class="col-xl-6 col-lg-3 col-sm-6">
                                        <div class="info-box card rounded-50">
                                            <i class="bi bi-geo-alt"></i>
                                            <h3>Address</h3>
                                            <p>{values?.address ?? ''}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-3 col-sm-6">
                                        <div class="info-box card rounded-50">
                                            <i class="bi bi-telephone"></i>
                                            <h3>Call Us</h3>
                                            <p>{values?.cell ?? ''}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-3 col-sm-6">
                                        <div class="info-box card rounded-50">
                                            <i class="bi bi-envelope"></i>
                                            <h3>Email Us</h3>
                                            <p>{values?.email ?? ''}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-3 col-sm-6">
                                        <div class="info-box card rounded-50">
                                            <i class="bi bi-clock"></i>
                                            <h3>Open Hours</h3>
                                            <p className={`text-capitalize mb-0`}>{`${(values?.week_start ?? '')} - ${(values?.week_end ?? '')} - ${(values?.hours_start ?? '')} - ${(values?.hours_end ?? '')}`}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                    <div className={`${supportDetails.length > 0 ? `col-xl-4` : `col-md-4 col-12`}`}>
                        <Chat />
                    </div>
                </div>
            </section>
        </>
    )
}

export default Support