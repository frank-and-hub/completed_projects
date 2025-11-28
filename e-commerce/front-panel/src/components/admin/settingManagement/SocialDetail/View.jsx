import React, { useEffect, useState } from 'react'
import { useDispatch } from 'react-redux'
import { useParams } from 'react-router-dom'
import { get } from 'utils/AxiosUtils'
import { processNotifications } from 'utils/notificationUtils'
import Input from 'components/admin/form/Input'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [serviceData] = await Promise.all([
                    get(`/social-details/${id}`),
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
            <Input name={`url`} text={`url`} label={`Url`} value={values?.url} required={false} inputType={true} disabled={true} />
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
