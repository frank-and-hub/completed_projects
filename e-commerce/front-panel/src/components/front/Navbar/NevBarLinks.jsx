import React from 'react'
import { Link } from 'react-router-dom'

function NevBarLinks() {
    return (
        <>
            <div>
                <Link className={`text-light`} to={`https://fb.com/templatemo`} target={`_blank`} rel={`sponsored`}> <i className={`fab fa-facebook-f fa-sm fa-fw me-2`}> </i></Link>
                <Link className={`text-light`} to={`https://www.instagram.com/`} target={`_blank`}> <i className={`fab fa-instagram fa-sm fa-fw me-2`}> </i></Link>
                <Link className={`text-light`} to={`https://twitter.com/`} target={`_blank`}> <i className={`fab fa-twitter fa-sm fa-fw me-2`}> </i></Link>
                <Link className={`text-light`} to={`https://www.linkedin.com/`} target={`_blank`}> <i className={`fab fa-linkedin fa-sm fa-fw`}> </i></Link>
            </div>
        </>
    );
}

export default NevBarLinks