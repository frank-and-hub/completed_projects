import React from 'react'
// import { Link } from 'react-router-dom'
import { SignUpForm } from './SignUpForm';
import Designedby from '../footer/designedby/Designedby';
// import { CaptureImage } from './CaptureImage';

function SignUp() {
    return (
        <>
            <div className={`container`}>
                <section className={`section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4`}>
                    <div className={`container`}>
                        <div className={`row justify-content-center`}>
                            <div className={`col-lg-5 col-md-7  d-flex flex-column align-items-center justify-content-center`}>
                                <div className={`d-flex justify-content-center`}>
                                                                        
                                    {/* <Link to={`/`} className={`logo d-flex align-items-center w-auto`}>
                                        <span className={`d-none d-lg-block`}>Admin</span>
                                    </Link> */}
                                   
                                    {/* <CaptureImage />  */}
                                </div>
                                <div className={`card mb-3`}>
                                    <div className={`card-body`}>
                                        <div className={`pt-4 pb-2`}>
                                            <h5 className={`card-title text-center pb-0 fs-4`}>Create an Admin Account</h5>
                                            <p className={`text-center small`}>Enter your personal details to create account</p>
                                        </div>
                                        <SignUpForm />
                                    </div>
                                </div>
                                <Designedby />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

export default SignUp;
