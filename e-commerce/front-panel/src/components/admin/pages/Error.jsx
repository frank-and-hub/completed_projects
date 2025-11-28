import React from 'react'
import { Link } from 'react-router-dom'


function Error() {
    return (
        <>
            <div className={`container`} >
                <section className={`section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center`} >
                    <h1>404</h1>
                    <h2>The page you are looking for doesn't exist.</h2>
                    <Link className={`btn text-capitalize`} to={`/admin`} >back to home</Link>
                    <img src={`/admin/img/not-found.svg`} className={`img-fluid py-5`} alt={`Page Not Found`} loading={`lazy`} />
                    <div className={`credits`}>
                        Designed by <a href={`https://bootstrapmade.com/`} >BootstrapMade</a>
                    </div>
                </section>
            </div>
        </>
    )
}

export default Error