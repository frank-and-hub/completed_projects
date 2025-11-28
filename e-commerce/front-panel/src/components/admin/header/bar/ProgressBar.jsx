import React from 'react'

export default function ProgressBar({ progress }) {
    
    return (
        <>
            <div className={`progress position-relative header fixed-top d-flex`} style={{ height: '.2rem' }}>
                <div class={`progress-bar`} role={`progressbar`} style={{ width: `${progress}%` }} aria-valuenow={`50`} aria-valuemin={`0`} aria-valuemax={`100`}></div>
            </div>
        </>
    )
}
