import React, { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { get } from 'utils/AxiosUtils'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/Textarea'
import { processNotifications } from 'utils/notificationUtils'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [getData] = await Promise.all([
                    get(`/categories/${id}`),
                ]);

                setValues(getData?.data || {});
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
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={false} inputType={true} disabled={true} />
            <div className={`col-md-4`}>
                <SelectIcon id="icon" value={values?.icon} handleChange={(e) => handleChange(e)} error={false} required={false} disabled={true} label='Icon' />
            </div>
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} required={false} inputType={true} disabled={true} ></Textarea>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
