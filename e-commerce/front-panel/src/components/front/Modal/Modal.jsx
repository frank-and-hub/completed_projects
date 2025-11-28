import React from 'react'
import LogInModel from './LogInModel';
import RegisterModel from './RegisterModel';
import ForgotPasswordModel from './ForgotPasswordModel';
import ResetPasswordModel from './ResetPasswordModel';
import OtpModel from './OtpModel';
import SearchModel from './SearchModel';

function Modal() {
    return (
        <>
            <SearchModel />
            <LogInModel />
            <RegisterModel />
            <ForgotPasswordModel />
            <ResetPasswordModel />
            <OtpModel />
        </>
    );
}

export default Modal