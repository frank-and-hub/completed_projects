import React from 'react'

function FooterForm() {
    return (
        <>
            <form className={`col-auto`}>
                <label className={`sr-only`} htmlFor={`subscribeEmail`}>Email address</label>
                <div className={`input-group mb-2`}>
                    <input type={`text`} className={`form-control bg-dark border-light`} id="subscribeEmail" placeholder="Email address" />
                    <div className={`input-group-text btn-success text-light`}>Subscribe</div>
                </div>
            </form>
        </>
    );
}

export default FooterForm