import React from 'react'
import { Link } from 'react-router-dom'
import "react-responsive-carousel/lib/styles/carousel.min.css"
import { Carousel } from 'react-responsive-carousel'

export default function FrontCarousel() {
    return (
        <>
            <div id="template-mo-zay-hero-carousel" className={`carousel slide" data-bs-ride="carousel`}>
                <Carousel showArrows={true} showThumbs={false} infiniteLoop autoPlay>
                    <div className={`container`}>
                        <div className={`row p-5`}>
                            <div className={`mx-auto col-md-8 col-lg-6 order-lg-last`}>
                                <img className={`img-fluid`} src="./assets/img/banner_img_01.jpg" alt="" />
                            </div>
                            <div className={`col-lg-6 mb-0 d-flex align-items-center`}>
                                <div className={`text-align-left align-self-center`}>
                                    <h1 className={`h1 text-success`}><b></b> eCommerce</h1>
                                    <h3 className={`h2`}>Tiny and Perfect eCommerce Template</h3>
                                    <p>
                                        Shop is an eCommerce HTML5 CSS template with latest version of Bootstrap 5 (beta 1).
                                        This template is 100% free provided by <Link rel="sponsored" className={`text-success`} to={`https://templatemo.com`} target={`_blank`}>TemplateMo</Link> website.
                                        Image credits go to <Link rel="sponsored" className={`text-success`} to={`https://stories.freepik.com/`} target={`_blank`}>Freepik Stories</Link>,
                                        <Link rel="sponsored" className={`text-success`} to={`https://unsplash.com/`} target={`_blank`}>Unsplash</Link> and
                                        <Link rel="sponsored" className={`text-success`} to={`https://icons8.com/`} target={`_blank`}>Icons 8</Link>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className={`container`}>
                        <div className={`row p-5`}>
                            <div className={`mx-auto col-md-8 col-lg-6 order-lg-last`}>
                                <img className={`img-fluid`} src="./assets/img/banner_img_02.jpg" alt="" />
                            </div>
                            <div className={`col-lg-6 mb-0 d-flex align-items-center`}>
                                <div className={`text-align-left`}>
                                    <h1 className={`h1`}>Proident occaecat</h1>
                                    <h3 className={`h2`}>Aliquip ex ea commodo consequat</h3>
                                    <p>
                                        You are permitted to use this CSS template for your commercial websites.
                                        You are <strong>not permitted</strong> to re-distribute the template ZIP file in any kind of template collection websites.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className={`container`}>
                        <div className={`row p-5`}>
                            <div className={`mx-auto col-md-8 col-lg-6 order-lg-last`}>
                                <img className={`img-fluid`} src="./assets/img/banner_img_03.jpg" alt="" />
                            </div>
                            <div className={`col-lg-6 mb-0 d-flex align-items-center`}>
                                <div className={`text-align-left`}>
                                    <h1 className={`h1`}>Repr in voluptate</h1>
                                    <h3 className={`h2`}>Ullamco laboris nisi ut </h3>
                                    <p>
                                        We bring you 100% free CSS templates for your websites.
                                        If you wish to support TemplateMo, please make a small contribution via PayPal or tell your friends about our website. Thank you.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Carousel>
            </div>
        </>
    );
}