import React from 'react'

function OtpModel() {
    return (
        <>
            <div className={`modal fade bg-white`} id={`templatemo_verify_otp`} tabIndex={`-1`} role={`dialog`} aria-labelledby={`exampleModalLabel`} aria-hidden={true}>
                <div className={`modal-dialog modal-lg`} role={`document`}>
                    <div className={`w-100 pt-1 mb-5 text-right position-relative`}>
                        <button type={`button`} className={`btn-close position-absolute top-0 start-0 btn`} data-bs-dismiss={`modal`} aria-label={`Close`}></button>
                    </div>
                    <form action={``} method={`POST`} className={`modal-content modal-body border-0 p-0`} id={`forgot_password_otp_form`} name={`forgot_password_otp_form`} >

                        <div className={`row mb-3`}>
                            <label className={`col-md-7 col-form-label text-md-end`}>Verify Your Otp</label>
                            <div className={`col-md-5`}>
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`otp`} className={`col-md-4 col-form-label text-md-end`}>Otp</label>

                            <div className={`col-md-6`} >
                                <input id={`otp`} type={`text`} className={`form-control`} name={`otp`} min={`6`} max={`6`} required autoComplete={`off`} />
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`forgot_otp_submit`} className={`col-md-4 col-form-label text-md-end`}></label>
                            <div className={`col-md-6`} >
                                <button type={`button`} className={`btn btn-outline-success w-100`} id={`forgot_otp_submit`} data-route={`/`} data-form-name={`forgot_password_otp_form`} >
                                    Varify Otp
                                </button>
                            </div>
                        </div>
                        <button className={`d-none`} id={`reset_password_model`} type={`reset`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_reset_password`} data-bs-dismiss={`modal`} aria-label={`Close`}>Reset Passcode</button>
                    </form>
                </div>
            </div>
        </>
    );
}

export default OtpModel