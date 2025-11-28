import React, { useEffect, useState } from 'react'
import Input from 'components/admin/form/Input'
import { useParams } from 'react-router-dom'
import SelectRole from 'components/admin/form/select/SelectRole'
import { get } from 'utils/AxiosUtils'
import { useDispatch } from 'react-redux'
import { processNotifications } from 'utils/notificationUtils'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                // Fetch roles and user data in parallel
                const [userData] = await Promise.all([
                    get(`/users/${id}`),
                ]);
                setValues(userData?.data || {});
                processNotifications(200, userData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };

        if (id) {
            fetchData();
        }
    }, [dispatch, id]);

    const handleChange = async (e) => {
        e.preventDefault();
    }

    return (
        <>
            {values && (
                <CardForm handleSubmit={(e) => e.preventDefault()} key={0}>
                    <Input name={`first_name`} label="User First Name" value={values?.name?.first_name} required={false} inputType={true} disabled={true} />
                    <Input name={`middle_name`} label="Middle Name" value={values?.name?.middle_name} required={false} inputType={true} disabled={true} />
                    <Input name={`last_name`} label="Last Name" value={values?.name?.last_name} required={false} inputType={true} disabled={true} />
                    <Input name={`email`} label="Email" type={`email`} value={values?.email} required={false} inputType={true} disabled={true} />
                    <Input name={`password`} label="Password" value={values?.password_text} required={false} inputType={true} disabled={true} />
                    <Input name={`phone`} label="Phone" value={values?.phone} required={false} inputType={true} disabled={true} />
                    <div className={`col-md-4`}>
                        <SelectRole id={`role_id`} value={values?.role?._id} required={false} disabled={true} label='role' handleChange={handleChange} />
                    </div>
                    <div className={`col-12`}>
                    </div>
                </CardForm>
            )}
        </>
    );
}

export default View
