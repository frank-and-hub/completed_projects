import React, { useCallback, useEffect, useTransition } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from 'utils/AuthContext'
import { useSelector } from 'react-redux'
import SubmitButton from 'components/admin/form/SubmitButton'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { generateRandomString } from 'utils/helper'
import { signUpValidation, useSignUpFormValidation } from 'utils/FormValidation'

export const SignUpForm = () => {

    const navigate = useNavigate();
    const { register } = useAuth();
    const [isPending, startTransition] = useTransition();
    const token = useSelector((state) => (state.auth.token));

    const initialState = {
        name: '',
        email: '',
        phone: '',
        password: ''
    }

    const { values, errors, handleChange, handleSubmit: validateSubmit } = useSignUpFormValidation(initialState, signUpValidation);

    const handleSubmit = useCallback(async (e) => {
        e.preventDefault();
        validateSubmit(e);

        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }

        startTransition(async () => {
            try {
                const { name, email, password, phone } = values;
                const response = await register(name, email, password, phone);

                if (!response) { throw new Error("Failed to submit form"); }

                const data = await response;
                notifySuccess(data.message);
                navigate('/admin/signin', { replace: true })
            } catch (error) {
                console.error(`Error submitting form: ${error}`);
            }
        });
    }, [values, errors, register, navigate, validateSubmit]);

    const generatePassword = () => {
        const password = generateRandomString();
        values.password = password;
        document.getElementById('yourPassword').value = password;
    }

    useEffect(() => {
        navigate(token ? '/admin' : '/admin/signup', { replace: true })
    }, [navigate, token]);

    return (
        <>
            <form encType={`multipart/form-data`} className={`row g-3`} onSubmit={handleSubmit}>
                <div className={`col-12`}>
                    <label htmlFor={`yourName`} className={`form-label`}>Your Name</label>
                    <input
                        type={`text`}
                        name={`name`}
                        className={`form-control`}
                        id="yourName"
                        value={values.name}
                        onChange={handleChange}
                        autoComplete="off"
                    />
                    {errors.name && <div className={`invalid-feedback d-block text-left`}>{errors.name}</div>}
                </div>

                <div className={`col-12`}>
                    <label htmlFor={`yourEmail`} className={`form-label`}>Your Email</label>
                    <div className={`input-group has-validation`}>
                        <span className={`input-group-text`} id={`inputGroupPrepend`}>@</span>
                        <input
                            type={`email`}
                            name={`email`}
                            className={`form-control`}
                            id="yourEmail"
                            value={values.email}
                            onChange={handleChange}
                            autoComplete="off"
                        />
                        {errors.username && <div className={`invalid-feedback d-block text-left`}>{errors.username}</div>}
                    </div>
                </div>

                <div className={`col-12`}>
                    <label htmlFor={`yourPhone`} className={`form-label`}>Phone Number</label>
                    <input
                        type={`text`}
                        name={`phone`}
                        className={`form-control`}
                        id="yourPhone"
                        value={values.phone}
                        onChange={handleChange}
                        autoComplete="off"
                    />
                    {errors.phone && <div className={`invalid-feedback d-block text-left`}>{errors.phone}</div>}
                </div>

                <div className={`col-12 position-relative`}>
                    <label htmlFor={`yourPassword`} className={`form-label`}>Password</label>
                    <input
                        type={`text`}
                        name={`password`}
                        className={`form-control`}
                        id="yourPassword"
                        value={values.password}
                        onChange={handleChange}
                        autoComplete="off"
                        readOnly={true}
                    />
                    <button type={`button`} data-toggle={`tooltip`} title={`Generate Key`} onClick={generatePassword} className='position-absolute bg-transparent' style={{ top: '55%', right: '5%' }}>
                        <i className='bi bi-key-fill'></i>
                    </button>
                    {errors.password && <div className={`invalid-feedback d-block text-left`}>{errors.password}</div>}
                </div>

                <div className={`col-12`}>
                    <SubmitButton className={`custom w-50`} disable={isPending} name={isPending ? 'Creating Account' : 'Create Account'} />
                </div>
                <div className={`col-12`}>
                    <p className={`small mb-0`}>Already have an account? <Link to={`/admin/signin`}>Sign in</Link></p>
                </div>
            </form>
        </>
    )
}