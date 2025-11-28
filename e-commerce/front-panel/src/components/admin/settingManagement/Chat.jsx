import React, { useEffect, useState } from 'react'
import { notifyError } from 'components/admin/comman/notification/Notification'
// import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'

// import { post } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import SelectForm from 'components/admin/form/select/SelectForm'
import { ucwords } from 'utils/helper'
import { get, post } from 'utils/AxiosUtils'
import { useSelector } from 'react-redux'
import { Messages } from './Messages'
import { chatValidation, useFormValidation } from 'utils/FormValidation'

const Chat = ({ userOptions }) => {

    const { loading, setLoading } = useLoading();
    const [formKey] = useState(0);
    const [userData, setUserData] = useState([]);
    const [selectedReceiver, setSelectedReceiver] = useState(null);

    const initialState = {
        receiver_id: '',
        message: ''
    }

    const roles = useSelector((state) => (state?.role?.role));

    const support_role_id = roles?.data
        ?.filter((option) => option.name === 'Support')
        ?.map((option) => option.id)[0];

    const {
        formData: values,
        errors,
        handleChange,
        handleSubmit: validateSubmit,
        setFormData: setValues
    } = useFormValidation(initialState, chatValidation);

    const handleSubmit = async (e) => {
        
        e.preventDefault();
        validateSubmit(e);

        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            const res = await post('/messages', values);
            if (res) {
                setValues({
                    receiver_id: values?.receiver_id,
                    message: ''
                });
                // fetchPrevoiusChat({ target: { value: values.receiver_id } });
            }
        } catch (err) {
            notifyError(err.message)
        } finally {
            setLoading(false)
        }
    };

    useEffect(() => {
        const fetchData = async () => {
            try {
                const res = await get(`/users?role_id=${support_role_id}`);
                const userOptions = res?.response?.data?.map((item) => ({
                    value: item?.id,
                    label: `${ucwords(item?.name)}`
                }));

                setUserData(userOptions || []);
            } catch (err) {
                console.error(err.message);
                setUserData([]);
            }
        };

        if (support_role_id) {
            fetchData();
        }
    }, [support_role_id]);

    return (
        <>
            <div className={`card p-4`}>
                <div className={`card-body`}>
                    <form key={formKey} encType={`multipart/form-data`} className={`w-100 needs-validation`} onSubmit={handleSubmit} noValidate>
                        <div className={`gy-4 w-100`}>

                            <div className={`col-md-12`}>
                                <SelectForm id={`receiver_id`} label={`Support`} value={values.receiver_id} handleChange={(e) => { handleChange(e); setSelectedReceiver(e?.target?.value) }} error={errors.receiver_id} required={true} Options={userData} />
                            </div>

                            <Messages isDataChange={selectedReceiver} />

                            <div className={`col-md-12`} >
                                <input
                                    type={`text`}
                                    name={`message`}
                                    className={`form-control ${errors.message ? 'is-invalid' : ''}`}
                                    defaultValue={values?.message}
                                    id={`message`}
                                    placeholder={`message...`}
                                    onChange={handleChange}
                                    required
                                />
                                {errors?.message && <div className={`invalid-feedback`} >{errors.message}</div>}
                            </div>

                            <div className={`col-12`}>
                                <SubmitButton className={`custom w-50`} disable={loading} name={loading ? 'Sending...' : 'Send'} />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <style jsx>{`
            .chat-container {
                max-height: 350px;
                overflow-y: scroll;
                scrollbar-width: none;
                border: 1px solid #ddd;
                padding: 10px;
                margin-bottom: 10px;
                margin-top: 10px;
                border-radius: 5px;
            }
            .chat-message {
                margin-bottom: 10px;
                padding: 10px;
                border-radius: 10px;
                display: inline-block;
                max-width: 75%;
            }
            .sender {
                background-color: #d1e7dd;
                align-self: flex-end;
                text-align: right;
            }
            .receiver {
                background-color: #f8d7da;
                align-self: flex-start;
                text-align: left;
            }
            .no-chat-message {
                text-align: center;
                color: #888;
            }
        `}</style>
        </>
    );
}

export default Chat;