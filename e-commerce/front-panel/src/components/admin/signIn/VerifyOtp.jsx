import React, { useTransition } from 'react'
// import { useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from 'utils/AuthContext';
import { otpValidation, useFormValidation } from 'utils/FormValidation';
import { notifySuccess } from 'components/admin/comman/notification/Notification';
import SubmitButton from 'components/admin/form/SubmitButton';
import Designedby from 'components/admin/footer/designedby/Designedby';
import OTPInput from '../form/inputs/OTPInput';


export default function VerifyOtp() {
    const navigate = useNavigate();
    // const token = useSelector((state) => (state.auth.token));

    const loginMethod = localStorage.getItem('user-login-method');
    const { varify_otp } = useAuth();
    const [isPending, startTransition] = useTransition();

    const initialState = {
        input: localStorage.getItem(`user-${loginMethod}`),
        otp: localStorage.getItem(`user-login-otp`),
        type: loginMethod,
    }

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit } = useFormValidation(initialState, otpValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        validateSubmit(e);

        if (errors && Object.keys(errors).length !== 0) {
            console.table(errors);
            return false;
        }

        startTransition(async () => {
            try {
                const response = await varify_otp(values.input, values.otp, values.type);
                if (!response) throw new Error("Failed to submit form");
                notifySuccess(response.message);
                navigate('/admin/index', { replace: true })
            } catch (error) {
                console.error(`Error during login: ${error}`);
            }
        });
    }

    // useEffect(() => {
    //     navigate(token ? '/admin' : '/admin/signin', { replace: true })
    // }, [token, navigate]);

    return (
        <>
            <div className={`container`}>
                <section className={`section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4`}>
                    <div className={`container`}>
                        <div className={`row justify-content-center`}>
                            <div className={`col-lg-5 col-md-7 d-flex flex-column align-items-center justify-content-center`}>
                                <div className={`d-flex justify-content-center py-4`}>
                                    <Link to={`/`} className={`logo d-flex align-items-center w-auto`} >
                                        <span className={`d-none d-lg-block`}></span>
                                    </Link>
                                </div>
                                <div className={`card mb-3`}>
                                    <div className={`card-body`}>
                                        <div className={`pt-4 pb-2`}>
                                            <h5 className={`card-title text-center pb-0 fs-4`}>Verify OTP for Your Account</h5>
                                        </div>

                                        <form encType={`multipart/form-data`} className={`row mt-3 g-3 needs-validation`} onSubmit={handleSubmit} noValidate>
                                            <div className={`col-12`}>
                                                <label htmlFor={`yourInput`} className={`form-label`}>User</label>
                                                <div className={`input-group has-validation`}>
                                                    <input
                                                        type={`input`}
                                                        name={`input`}
                                                        className={`form-control`}
                                                        id="yourInput"
                                                        value={values?.input ?? null}
                                                        onChange={handleChange}
                                                        autoComplete="off"
                                                        readOnly={true}
                                                    />
                                                    {errors.input && <div className={`invalid-feedback d-block`}>{errors.input}</div>}
                                                </div>
                                            </div>

                                            <div className={`col-12`}>
                                                <label htmlFor={`yourOtp`} className={`form-label`}>OTP</label>
                                                <OTPInput
                                                    value={values.otp}
                                                     type={`otp`}
                                                    name={`otp`}
                                                    onChange={(val) => handleChange({ target: { name: 'otp', value: val } })}
                                                />
                                                {errors.otp && <div className={`invalid-feedback d-block`}>{errors.otp}</div>}
                                            </div>
                                            <input
                                                type={`type`}
                                                name={`type`}
                                                className={`form-control`}
                                                value={values.type}
                                                onChange={handleChange}
                                                autoComplete="off"
                                                hidden={true}
                                            />
                                            <div className={`col-12`}>
                                                <SubmitButton className={`custom w-50`} disable={isPending} name={isPending ? 'Verifying...' : 'Verify'} />
                                            </div>
                                            <div className={`col-12`}>
                                                <p className={`small mb-0`}><Link to={`/admin/signin`}>Sign in</Link></p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <Designedby />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </>
    )
}
