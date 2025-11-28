import React, { useContext, useEffect, useState } from 'react'
import { destroy, get } from 'utils/AxiosUtils'
import AccordionFrom from './AccordionFrom'
import { useNavigate } from 'react-router-dom'
import { SidebarContext } from 'context/SidebarContext'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { useLoading } from 'context/LoadingContext'
import { Loading } from 'components/admin/loading/Loading'
import ReusableModal from 'components/admin/models/ReusableModal'

function List() {
    const { pathname } = useContext(SidebarContext);
    const [activeIndex, setActiveIndex] = useState(null);
    const [faqData, setFaqData] = useState();
    const [modalShow, setModalShow] = useState(false);
    const [itemToDelete, setItemToDelete] = useState(null);
    const { loading, setLoading } = useLoading();
    const [accordionType, setAccordionType] = useState(false);
    const navigate = useNavigate();

    const onClickSetState = (index) => {
        setActiveIndex(activeIndex === index ? null : index);
    }

    const handleDeleteConfirm = async () => {
        setLoading(true)
        try {
            const resDeleted = await destroy(`/faq/${itemToDelete}`);
            notifySuccess(resDeleted.message);
        } catch (err) {
            console.error(err.message);
        } finally {
            setLoading(false)
        }
        setModalShow(false);
        setItemToDelete(null);
    };

    const handelDelete = (e) => {
        console.info(`delete item ${e}`)
        setItemToDelete(e);
        setModalShow(true);
    }

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true)
            try {
                const [reqData] = await Promise.all([
                    get(`/faq`),
                ]);

                setFaqData(reqData?.response?.data || {});
            } catch (err) {
                console.error(err.message);
            } finally {
                setLoading(false)
            }
        };
        fetchData();
    }, [setLoading, pathname]);

    return (
        <>
            <section className={`section`}>
                <div className={`card-head`}>
                    <div className={`card-title`}>
                        <p className='py-0 m-0 btn' onClick={() => (navigate('/admin/settings/faqs/create', true))} > Frequently Asked Questions  </p>
                    </div>
                </div>
                <div className={`row`}>
                    <div className={`col-md-12`}>
                        {loading ? (
                            <Loading />
                        ) : (faqData && faqData?.length > 0) ? faqData?.map((item, index) => (
                            <div key={item?.id} className={`card`}>
                                <div className={`card-body`}>
                                    <div className={`accordion accordion-flush pt-1`} id={`faq-group-${index}`}>
                                        <div className={`accordion-item`}>
                                            <h2 className={`accordion-header`}>
                                                <button
                                                    className={`accordion-button collapsed`}
                                                    data-bs-target={`#faqs${index}-${item?.id}, #faqs${index}-${item?.id}-edit-delete`}
                                                    type={`button`}
                                                    data-bs-toggle="collapse"
                                                    onClick={() => onClickSetState(`${item?.id}-faq-${index}`)}>
                                                    {item?.question}
                                                </button>
                                            </h2>
                                            <div
                                                id={`faqs${index}-${item?.id}`}
                                                className={`accordion-collapse collapse ${activeIndex === (`${item?.id}-faq-${index}`) ? 'show' : ''}`}
                                                data-bs-parent={`#faq-group-${index}`}>
                                                <div className={`accordion-body`} style={{ textAlign: "left" }}>
                                                    {accordionType ? (
                                                        <AccordionFrom value={item} onAction={setAccordionType} />
                                                    ) : (item?.answer)}
                                                </div>
                                                <div className={`collapse ${activeIndex === (`${item?.id}-faq-${index}`) ? 'show' : ''} col-12 text-end`} id={`faqs${index}-${item?.id}-edit-delete`} >
                                                    <button
                                                        type={`button`}
                                                        className='btn border-0 py-0 my-0'
                                                        onClick={() => setAccordionType(!accordionType)} >
                                                        <i className='bi bi-pencil-square'></i>
                                                    </button>
                                                    <button
                                                        type={`button`}
                                                        className='btn border-0 py-0 my-0'
                                                        onClick={(e) => handelDelete(`${item?.id}`)} >
                                                        <i className='bi bi-trash'></i>
                                                    </button>
                                                </div>
                                                <button className={`m-0 p-0`} data-bs-toggle="collapse" data-bs-target={`#faqs${index}-${item?.id}-edit-delete`} >
                                                    <i className='d-none'></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )) : (<>No data available...</>)}
                    </div>
                </div>
            </section>
            <ReusableModal
                show={modalShow}
                handleClose={() => setModalShow(false)}
                title="Confirm Delete"
                body="Are your want to delete Item"
                primaryAction={handleDeleteConfirm}
                primaryLabel="Delete"
                secondaryLabel="Cancel"
            />
        </>
    )
}

export default List