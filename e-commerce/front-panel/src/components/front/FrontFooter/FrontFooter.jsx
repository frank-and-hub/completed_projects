import React from 'react'
import { Link } from 'react-router-dom'
import FooterForm from './FooterForm'
import FooterLink from './FooterLink'
import FooterNavBar from './FooterNavBar'
import FooterTopCategory from './FooterTopCategory'
import FooterAddress from './FooterAddress'

function FrontFooter() {
    return (
        <>
            <footer className={`bg-dark`} id={`tempaltemo_footer`}>
                <div className={`container`}>
                    <div className={`row`}>
                        <FooterAddress />
                        <FooterTopCategory />
                        <FooterNavBar />
                    </div>
                    <div className={`row text-light mb-4`}>
                        <div className={`col-12 mb-3`}>
                            <div className={`w-100 my-3 border-top border-light`}></div>
                        </div>
                        <FooterLink />
                        <FooterForm />
                    </div>
                </div>

                <div className={`w-100 bg-dark py-3`}>
                    <div className={`container`}>
                        <div className={`row pt-2`}>
                            <div className={`col-12`}>
                                <p className={`text-left text-light`}>
                                    Copyright &copy; {new Date().getFullYear()} | Designed by <Link rel={`sponsored`} onClick={(e) => e.preventDefault()} target={`_blank`}> Frank And Hub</Link>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </>
    );
}

export default FrontFooter