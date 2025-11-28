import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import { useParams } from 'react-router-dom'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch, useSelector } from 'react-redux'
import CardForm from 'components/admin/card/CardForm'
import SelectForm from 'components/admin/form/select/SelectForm'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import { ucwords } from 'utils/helper'

function View() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);
    const [hodUserData, setHodUSerData] = useState([]);
    const [employeeUserData, setEmployeeUserData] = useState([]);

    const roles = useSelector((state) => (state?.role?.role));

    const hod_role_id = roles?.data
        ?.filter((option) => option.name === 'hod')
        ?.map((option) => option.id)[0];

    const employee_role_id = roles?.data
        ?.filter((option) => option.name === 'employee')
        ?.map((option) => option.id)[0];

    useEffect(() => {
        const fetchData = async () => {
            try {

                const [roleRes, employeeRes] = await Promise.all([
                    get(`/users?role_id=${hod_role_id}`),
                    get(`/users?role_id=${employee_role_id}`),
                ]);

                const userRoleOptions = roleRes?.response?.data?.map((item) => ({
                    value: item?.id,
                    label: `${ucwords(item?.name)}`
                }));

                const userEmployeeOptions = employeeRes?.response?.data?.map((item) => ({
                    value: item?.id,
                    label: `${ucwords(item?.name)}`
                }));

                setHodUSerData(userRoleOptions || []);
                setEmployeeUserData(userEmployeeOptions || []);
            } catch (err) {
                console.error(err.message);
                setHodUSerData([]);
            }
        };

        fetchData();
    }, [hod_role_id, employee_role_id]);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [roleData] = await Promise.all([
                    get(`/departments/${id}`),
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
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={true} inputType={true} disabled={true}/>
            <div className={`col-md-4`}>
                <SelectIcon id="icon" label={`Icon`} value={values?.icon} handleChange={handleChange} required={true} disabled={true}/>
            </div>
            <div className={`col-md-4`}>
                <SelectForm id="hod_id" label={`h o d`} value={values?.hod_id} handleChange={handleChange} required={true} Options={hodUserData} disabled={true}/>
            </div>
            <div className={`col-md-4`}>
                <SelectForm id="members" label={`members`} value={values?.members} handleChange={handleChange} required={true} Options={employeeUserData} isMulti={true} disabled={true}/>
            </div>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
