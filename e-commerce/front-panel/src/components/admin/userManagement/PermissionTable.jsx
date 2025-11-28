import React, { useEffect, useState } from 'react'
import SubmitButton from 'components/admin/form/SubmitButton'
import { useLoading } from 'context/LoadingContext'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { post } from 'utils/AxiosUtils'
import { transformData, ucwords } from 'utils/helper'
import { useNavigate } from 'react-router-dom'
import { useFormValidation, validate } from 'utils/FormValidation'

export const PermissionTable = ({ response, permission, user_id = null, mainFormReset, permissionsName }) => {
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const [toggle, setToggle] = useState(false);
    const navigate = useNavigate();
    const columnCount = `6`;
    const initialState = {};

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, validate);

    const handleSubmit = async (e) => {
        e.preventDefault();
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            if (user_id) {
                const res = await post(`/users/${user_id}/permssions`, transformData(values));
                notifySuccess(res.message)
            }
            navigate(`/admin/users/permissions`, { replace: true })
        } catch (err) {
            console.error(err.message);
        } finally {
            setLoading(false)
            resetForm()
            mainFormReset()
        }
    };

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName(`form`)[0].reset();
    };

    useEffect(() => {
        if (response?.length > 0) {
            const initialValues = response.reduce((acc, curr, k) => {
                acc[`menu[${k}]`] = curr.id;
                return acc;
            }, {});
            setValues((prev) => ({ ...prev, ...initialValues }));
        }
    }, [response, setValues, user_id]);

    return (
        <>
            <div className={`card`} >
                <div className={`card-head`} ></div>
                <div className={`card-body`} >
                    <form key={formKey} className={`row mt-3 px-3 g-4 needs-validation overflow-auto`} onSubmit={handleSubmit} noValidate >
                        <table className={`table table-borderless table-sm datatable table-responsive{-sm|-md|-lg|-xl} mb-2`} >
                            <thead>
                                <tr key={formKey}>
                                    <th><i className={`bi bi-hash`}></i></th>
                                    <th>Menu Name</th>
                                    {permissionsName.length > 0 && permissionsName.map((value, key) => (
                                        <th key={key}>{ucwords(value)}</th>
                                    ))}
                                    <th className={`p-0 text-center`}>
                                        <i data-toggle={`tooltip`}
                                            title={`Checked All`}
                                            className={`bi bi-toggle-${toggle ? 'on' : 'off'} coursor-pointer`}
                                            onClick={(e) => setToggle(!toggle)}
                                            style={{ fontSize: '1.5rem' }}
                                        ></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {response && response.length > 0 ? (
                                    response.map((value, k) => {
                                        const isChecked = (field) => permission?.[k]?.[field] === true;
                                        return (<tr key={k}>
                                            <td>{k + 1}<input name={`menu[${k}]`} onChange={handleChange} type={`hidden`} defaultValue={value?.id} className={`${k} ${toggle}`} /> </td>
                                            <td>{ucwords(value?.name)}</td>
                                            {permissionsName.length > 0 && permissionsName.map((value, key) => (
                                                <td><input name={`${value}[${k}]`} onChange={handleChange} type={`checkbox`} defaultChecked={isChecked(`${value}`)} defaultValue={`1`} className={`btn`} /></td>
                                            ))}
                                            <th></th>
                                        </tr>)
                                    })) : (<tr>
                                        <th colSpan={columnCount} >No data available...</th>
                                    </tr>)}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colSpan={columnCount}>
                                        <div className={`col-12`}>
                                            {response && (
                                                <SubmitButton className={`custom`} disable={loading} name={loading ? `Submitting...` : `Submit Form`} />
                                            )}
                                        </div>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
        </>
    );
}

export default PermissionTable