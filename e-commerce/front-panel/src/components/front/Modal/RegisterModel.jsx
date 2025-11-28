import React from 'react'

function RegisterModel() {
    return (
        <>
            <div className={`modal fade bg-white`} id={`templatemo_register`} tabIndex={`-1`} role={`dialog`} aria-labelledby={`exampleModalLabel`} aria-hidden={true}>
                <div className={`modal-dialog modal-lg`} role={`document`}>
                    <div className={`w-100 pt-1 mb-5 text-right position-relative`}>
                        <button type={`button`} className={`btn-close position-absolute top-0 start-0 btn`} data-bs-dismiss={`modal`} aria-label={`Close`}></button>
                    </div>

                    <form action={``} method={`POST`} className={`modal-content modal-body border-0 p-0`} id={`register_form_font`} name={`register_form_font`} >
                        <div className={`row mb-3`}>
                            <label className={`col-md-12 col-form-label text-md-center`}>Create new account</label>
                            <div className={`col-md-5`}></div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`name`} className={`col-md-4 col-form-label text-md-end`}>Name</label>

                            <div className={`col-md-6`} >
                                <input id={`name`} type={`text`} className={`form-control`} name={`name`} defaultValue={``} required autoComplete={`off`} autoFocus />
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`email`} className={`col-md-4 col-form-label text-md-end`}>Email Address</label>

                            <div className={`col-md-6`} >
                                <input id={`email`} type={`email`} className={`form-control `} name={`email`} defaultValue={``} required autoComplete={`off`} />
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`password`} className={`col-md-4 col-form-label text-md-end`}>Password</label>

                            <div className={`col-md-6`} >
                                <input id={`password`} type={`password`} className={`form-control`} name={`password`} required autoComplete={`off`} />
                            </div>
                        </div>

                        <div className={`mb-3 col-12 row w-100`}>
                            <label htmlFor={`password-confirm`} className={`col-md-4 col-form-label text-md-end`}>Confirm Password</label>

                            <div className={`col-md-6`} >
                                <input id={`password-confirm`} type={`password`} className={`form-control`} name={`password_confirmation`} required autoComplete={`off`} />
                            </div>
                        </div>
                        <div className={`mb-3 col-12 row w-100`}>
                            <div className={`col-md-10 offset-md-2`}>
                                <button type={`submit`} className={`btn btn-outline-success w-25 mx-1`} to={`#`} data-bs-toggle={`modal`} data-bs-target={`#templatemo_login`} data-bs-dismiss={`modal`} aria-label={`Close`}>
                                    Login
                                </button>
                                <button type={`button`} className={`btn btn-outline-success w-25 mx-1`} >
                                    Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div >
            </div >
        </>
    );
}

export default RegisterModel