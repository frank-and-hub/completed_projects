import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import { useParams } from 'react-router-dom'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch } from 'react-redux'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'
import SelectSupplier from 'components/admin/form/select/SelectSupplier'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [storesData] = await Promise.all([
                    get(`/stores/${id}`),
                ]);
                setValues(storesData?.data || {});
                processNotifications(200, storesData?.message, dispatch);
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
            <Input name={`name`} label="name" value={values?.name} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`phone`} label="phone" value={values?.phone} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`email`} label="email" value={values?.email} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`country`} label="country" value={values?.country} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`state`} label="state" value={values?.state} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`city`} label="city" value={values?.city} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <Input name={`zipcode`} label="zipcode" value={values?.zipcode} onChange={handleChange} disabled={true} required={false} inputType={true} />
            <div className={`col-md-4`}>
                <SelectSupplier id={`supplier_id`} label={`supplier`} value={values?.supplier_id} handleChange={handleChange} required={true} disabled={true}/>
            </div>
            <Textarea name={`address`} className={`w-100`} label="address" value={values?.address} onChange={handleChange} disabled={true} required={false} inputType={true} ></Textarea>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
