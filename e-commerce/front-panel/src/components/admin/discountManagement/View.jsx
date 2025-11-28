import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import { useParams } from 'react-router-dom'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch } from 'react-redux'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {

                const [tagData] = await Promise.all([
                    get(`/discounts/${id}`),
                ]);

                setValues(tagData?.data || {});

                processNotifications(200, tagData?.message, dispatch);
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
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Input name={`percentage`} label="percentage" value={values?.percentage} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} required={false} inputType={true} disabled={true} ></Textarea>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
