import React from 'react'

function ResetPasswordModel() {
    return (
        <>
            <div className={`modal fade bg-white`} id={`templatemo_reset_password`} tabIndex={`-1`} role={`dialog`} aria-labelledby={`exampleModalLabel`} aria-hidden={true}>
                <div className={`modal-dialog modal-le`} role={`document`}>
                    <div className={`w-100 pt-1 mb-5 text-right position-relative`}>
                        <button type={`button`} className={`btn-close position-absolute top-0 start-0 btn`} data-bs-dismiss={`modal`} aria-label={`Close`}></button>
                    </div>
                    <form action={``} method={`post`} className={`modal-content modal-body border-0 p-0`} id={`forgot_reset_password_form`} name={`forgot_reset_password_form`} >
                        <div className={`row mb-3`}>
                            <label className={`col-md-7 col-form-label text-md-end`}>Add New Password</label>
                            <div className={`col-md-5`}>
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`new_password`} className={`col-md-4 col-form-label text-md-end`}>New Password</label>

                            <div className={`col-md-6`} >
                                <input id={`new_password`} type={`password`} className={`form-control`} name={`new_password`} required autoComplete={`off`} />
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`confirm_new_password`} className={`col-md-4 col-form-label text-md-end`}>Confirm New Password</label>

                            <div className={`col-md-6`} >
                                <input id={`confirm_new_password`} type={`text`} className={`form-control`} name={`confirm_new_password`} required autoComplete={`off`} />

                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`reset_password_submit`} className={`col-md-4 col-form-label text-md-end`}></label>
                            <div className={`col-md-6`} >
                                <button type={`button`} className={`btn btn-outline-success w-100`} id={`reset_password_submit`} data-route={`/`} data-form-name={`forgot_reset_password_form`} >
                                    Reset Password
                                </button>
                            </div>
                        </div>
                        <button className={`d-none`} id={`verify_code_reset`} type={`reset`} data-bs-toggle={`modal`} data-bs-dismiss={`modal`} aria-label={`Close`}>Verify Code</button>
                    </form>
                </div>
            </div>
        </>
    );
}

export default ResetPasswordModel