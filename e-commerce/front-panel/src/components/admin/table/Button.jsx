import React from 'react'
import { ucwords } from 'utils/helper'
import { Link } from 'react-router-dom'

function Button({ iconClass, onClick = null, tooltip = null, url = null, disabled = false }) {
    const classValue = `btn btn-light m-auto btn-md p-0 bg-transparent border-0 rounded-circle`;
    return (
        <>
            {tooltip ? (
                <span className={`d-inline-block`} tabIndex="0" data-toggle="tooltip" title={ucwords(tooltip)}>
                    {url ? (<Link to={url} className={classValue} disabled={disabled}>
                        <i className={iconClass}></i>
                    </Link>) : (
                        <button onClick={onClick ?? ((e) => (e.preventDefault()))} className={classValue} disabled={disabled}>
                            <i className={iconClass}></i>
                        </button>)}
                </span>
            ) : (
                <>
                    {url ? (<Link to={url} className={classValue} disabled={disabled}>
                        <i className={iconClass}></i>
                    </Link>) : (
                        <button onClick={onClick ?? ((e) => (e.preventDefault()))} className={classValue} disabled={disabled}>
                            <i className={iconClass}></i>
                        </button>)}
                </>
            )}
        </>
    );
}

export default Button;
