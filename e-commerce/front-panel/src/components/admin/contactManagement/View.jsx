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

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [testimonialData] = await Promise.all([
                    get(`/contacts/${id}`),
                ]);
                setValues(testimonialData?.data || {});
                processNotifications(200, testimonialData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, setValues, id]);

    const handleChange = (e) => {
        e.preventDefault()
    }

    return (
        <CardForm handleSubmit={(e) => e.preventDefault()} key={0}>
            <Textarea onChange={handleChange} className={`w-100`} name={`message`} value={values?.message} label={`Message`} required={false} disabled={true} />
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Input name={`email`} label="Email" value={values?.email} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
