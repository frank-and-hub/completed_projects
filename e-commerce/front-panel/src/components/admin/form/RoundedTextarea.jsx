import React from 'react'
import { rasc } from 'utils/helper'

function RoundedTextarea({ name, label, type = 'text', value, onChange, error, required = false, inputType = false, ...props }) {

    const TextAreaItem = <textarea name={name} type={type} className={`form-control rounded-pill${error ? ' is-invalid' : ''}`} id={rasc(name)} defaultValue={value} style={{ height: "100px", paddingTop: "10px" }} onChange={onChange} {...props}></textarea>;

    return (
        <>
            <div className={`${inputType ? 'col-md-4' : 'row mb-3'}`} >
                <label htmlFor={rasc(name)} className={`col-md-4 col-lg-3 col-form-label text-start text-capitalize`}>{(label)} {required && <span className={`text-danger`}>*</span>}</label>
                {inputType ? TextAreaItem : <div className={`col-md-8 col-lg-9`}>{TextAreaItem}</div>}
                {error && <div className={`invalid-feedback`}>{error}</div>}
            </div>
        </>
    )
}

export default RoundedTextarea