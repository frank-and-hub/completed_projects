import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/Textarea'
import { useParams } from 'react-router-dom'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch } from 'react-redux'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);
    const [src, setSrc] = useState(``);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [serviceData] = await Promise.all([
                    get(`/brands/${id}`),
                ]);

                setValues(serviceData?.data || {});
                setSrc(serviceData?.data?.image?.path);
                processNotifications(200, serviceData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, id]);

    const handleChange = (e) => {
        e.preventDefault()
    }

    return (
        <CardForm handleSubmit={(e) => e.preventDefault()} key={0}>
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Brand`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={(e) => e.preventDefault()} loading={`lazy`} />
                </div>
            </div>
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={(e) => e.preventDefault()} />
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} required={false} inputType={true} disabled={true} ></Textarea>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
