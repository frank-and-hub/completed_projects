import React from 'react'
import { Link } from 'react-router-dom'

function Designedby() {
    return (
        <>
            <div className={`credits pt-0 pb-2`}>
                Designed by  <Link onClick={(e) => e.preventDefault()}> Frank And Hub </Link>
            </div>
        </>
    )
}

export default Designedby