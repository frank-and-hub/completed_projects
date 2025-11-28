import React from 'react'

function ForgotPasswordModel() {
    return (
        <>
            <div className={`modal fade bg-white`} id={`templatemo_forget_password`} tabIndex={`-1`} role={`dialog`} aria-labelledby={`exampleModalLabel`} aria-hidden={true}>
                <div className={`modal-dialog modal-lg`} role={`document`}>
                    <div className={`w-100 pt-1 mb-5 text-right position-relative`}>
                        <button type={`button`} className={`btn-close position-absolute top-0 start-0 btn`} data-bs-dismiss={`modal`} aria-label={`Close`}></button>
                    </div>
                    <form action={``} method={`POST`} className={`modal-content modal-body border-0 p-0`} id={`forgot_password_form`} name={`forgot_password_form`} >
                        <div className={`row mb-3`}>
                            <label className={`col-md-12 col-form-label text-md-center`}>Reset Your Password</label>
                            <div className={`col-md-5`}>
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`email`} className={`col-md-4 col-form-label text-md-end`}>Email Address</label>
                            <div className={`col-md-6`} >
                                <input id={`email`} type={`email`} className={`form-control`} name={`email`} required autoComplete={`off`} />
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`forgot_password_submit`} className={`col-md-4 col-form-label text-md-end`}></label>
                            <div className={`col-md-6`} >
                                <button type={`button`} className={`btn btn-outline-success w-100`} id={`forgot_password_submit`} data-route={`/`} data-form-name={`forgot_password_form`} >
                                    Send Password Reset Link
                                </button>
                            </div>
                        </div>
                        <button className={`d-none`} id={`verify_code`} type={`reset`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_verify_otp`} data-bs-dismiss={`modal`} aria-label={`Close`}>Verify Code</button>
                    </form>
                </div>
            </div>
        </>
    );
}

export default ForgotPasswordModel