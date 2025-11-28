import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import SelectIcon from 'components/admin/form/select/SelectIcon'
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
                const [serviceData] = await Promise.all([
                    get(`/services/${id}`),
                ]);

                setValues(serviceData?.data || {});
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
            <Input name={`name`} label="Name" value={values?.name} required={false} inputType={true} disabled={true} />
            <div className={`col-md-4`}>
                <SelectIcon id="icon" value={values?.icon} handleChange={(e) => handleChange(e)} label='Icon' required={false} disabled={true} />
            </div>
            <Textarea onChange={handleChange} name={`description`} value={values?.description} label={`Description`} required={false} disabled={true} />
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
