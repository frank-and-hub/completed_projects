import React, { useEffect, useState, useTransition } from 'react'
import { useNavigate } from 'react-router-dom';
import { useAuth } from 'utils/AuthContext';
import { notifySuccess } from 'components/admin/comman/notification/Notification';

function LogInModel() {

    const navigate = useNavigate();
    const { login } = useAuth();
    const [isPending, startTransition] = useTransition();

    const initialState = {
        email: '',
        password: '',
        remember: ''
    }

    function validate(values) {
        let errors = {}
        if (!values.email) {
            errors.email = 'Enter your Email';
        } else if (!/\S+@\S+\.\S+/.test(values.email)) {
            errors.email = 'Please enter a valid email address';
        }

        if (!values.password) {
            errors.password = 'Please enter your password';
        } else if (values.password.length < 6) {
            errors.password = 'Password needs to be at least 6 characters';
        }

        if (!values.password) {
            errors.password = 'Please enter your password';
        } else if (values.password.length < 6) {
            errors.password = 'Password needs to be at least 6 characters';
        }
        return errors;
    }

    const useFormValidation = (initialState, validate) => {
        const [values, setValues] = useState(initialState);
        const [errors, setErrors] = useState({});
        const [isSubmitting, setIsSubmitting] = useState(false);

        const handleChange = (e) => {
            const { name, value } = e.target;
            setValues({
                ...values,
                [name]: value
            });
        }

        const handleSubmit = (e) => {
            e.preventDefault();
            const validationErrors = validate(values);
            setErrors(validationErrors);
            setIsSubmitting(true);
        }

        return {
            values,
            errors,
            handleChange,
            handleSubmit,
            isSubmitting
        }
    }

    const { values, errors, handleChange, handleSubmit: validateSubmit } = useFormValidation(initialState, validate);

    const handleSubmit = async (e) => {
        e.preventDefault();
        validateSubmit(e);

        if (errors && Object.keys(errors).length !== 0) {
            console.table(errors);
            return false;
        }

        startTransition(async () => {
            try {
                const response = await login(values.email, values.password);
                if (!response) throw new Error("Failed to submit form");
                notifySuccess(response.message);
                navigate(`/`, { replace: true });
                document.querySelector('.btn-close').click();
            } catch (error) {
                console.error(`Error during login: ${error}`);
            }
        });
    }

    useEffect(() => {
        navigate(`/`, { replace: true })
    }, [navigate]);

    return (
        <>
            <div className={`modal fade bg-white`} id={`templatemo_login`} tabIndex={-1} role={`dialog`} aria-labelledby={`exampleModalLabel`} aria-hidden={true}>
                <div className={`modal-dialog modal-lg`} role={`document`}>
                    <div className={`w-100 pt-1 mb-5 text-right position-relative`}>
                        <button type={`button`} className={`btn-close position-absolute top-0 start-0 btn`} data-bs-dismiss={`modal`} aria-label={`Close`}></button>
                    </div>
                    <form className={`modal-content modal-body border-0 p-0`} method={`POST`} onSubmit={handleSubmit}>
                        <div className={`row mb-3`}>
                            <label className={`col-md-12 col-form-label text-md-center`}>Login</label>
                            <div className={`col-md-5`}></div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`email`} className={`col-md-4 col-form-label text-md-end`}>Email Address</label>
                            <div className={`col-md-6`} >
                                <input id={`email`} type={`email`} className={`form-control `} name={`email`} value={values.email} onChange={handleChange} required autoComplete={`off`} autoFocus />
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`password`} className={`col-md-4 col-form-label text-md-end`}>Password</label>

                            <div className={`col-md-6`} >
                                <input id={`password`} type={`password`} className={`form-control`} name={`password`} required autoComplete={`current-password`} value={values.password} onChange={handleChange} />
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <div className={`col-md-6 offset-md-4`}>
                                <div className={`form-check`}>
                                    <input className={`form-check-input success`} type={`checkbox`} name={`remember`} value={values.remember} id={`remember`} />
                                    <label className={`form-check-label`} htmlFor={`remember`}>
                                        Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <div className={`col-md-10 offset-md-2`}>
                                <button type={`submit`} className={`btn btn-outline-success w-25 mx-1`} >
                                    {isPending ? `Login` : 'Login...'}
                                </button>
                                <button type={`button`} className={`btn btn-outline-success w-25 mx-1`} to={`#`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_register`} data-bs-dismiss={`modal`} aria-label={`Close`}>
                                    Register
                                </button>
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <div className={`col-md-10 offset-md-2`}>
                                <button type={`button`} className={`btn btn-outline-success w-50`} to={`#`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_forget_password`} data-bs-dismiss={`modal`} aria-label={`Close`}>
                                    Forgot Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}

export default LogInModel