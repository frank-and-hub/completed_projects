import React from 'react'

function SubmitButton({ className, name, type = `submit`, disable = false, onClick = null }) {
    return (
        <>
            <button
                className={`col-xl-2 col-md-2 col-sm-3 col-xs-4 col-5 btn btn-outline-${className} btn-sm rounded-pill text-capitalize shadow`}
                type={type}
                disable={disable}
                onClick={onClick}
            >
                {name}
            </button>
        </>
    )
}

export default SubmitButton