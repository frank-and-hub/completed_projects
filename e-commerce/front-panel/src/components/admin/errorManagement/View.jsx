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
                const [errorLogData] = await Promise.all([
                    get(`/errors/${id}`),
                ]);
                setValues(errorLogData?.data || {});
                processNotifications(200, errorLogData?.message, dispatch);
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
            <Input name={`route`} label="route" value={values?.route} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Input name={`statusCode`} label="statusCode" value={values?.statusCode} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Input name={`errorType`} label="errorType" value={values?.errorType} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Input name={`ip`} label="ip" value={values?.ip} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <Textarea name={`errorMessage`} label="error Message" value={values?.errorMessage} onChange={handleChange} required={false} inputType={true} disabled={true}  className={`w-100`}></Textarea>
            <Textarea name={`stackTrace`} className={`w-100`} label={`stack Trace`} value={values?.stackTrace} onChange={handleChange} required={false} inputType={true} disabled={true} ></Textarea>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
