import React, { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/Textarea'
import { processNotifications } from 'utils/notificationUtils'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);
    const [src, setSrc] = useState(``);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [getData] = await Promise.all([
                    get(`/banners/${id}`),
                ]);

                setValues(getData?.data || {});
                setSrc(getData?.data?.image?.path);
                processNotifications(200, getData?.message, dispatch);
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
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={(e) => e.preventDefault()} />
            <Input name={`name`} label="name" value={values?.name} required={false} inputType={true} disabled={true} />
            <Input name={`title`} label="title" value={values?.title} required={false} inputType={true} disabled={true} />
            <Input name={`url`} label="url" value={values?.url} required={false} inputType={true} disabled={true} />
            <Textarea onChange={handleChange} className={`w-100`} name={`description`} value={values?.description} label={`Description`} required={false} disabled={true} />
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Banner`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={(e) => e.preventDefault()} loading={`lazy`}/>
                </div>
            </div>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
