import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import SelectPermission from 'components/admin/form/select/SelectPermission'
import Input from 'components/admin/form/Input'
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
                // Fetch roles and user data in parallel
                const [roleData] = await Promise.all([
                    get(`/roles/${id}`),
                ]);

                setValues(roleData?.data || {});

                processNotifications(200, roleData?.message, dispatch);
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
                <SelectPermission id="permission" value={values?.permissions} handleChange={(e) => handleChange(e)} label='Permissions' required={false} disabled={true} />
            </div>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
