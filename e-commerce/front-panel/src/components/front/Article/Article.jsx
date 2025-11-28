import React from 'react'
import { Link } from 'react-router-dom'

function Article() {

    return (
        <>
            <section className={`py-5`}>
                <div className={`container`}>
                    <div className={`row text-left p-2 pb-3`}>
                        <h4>Related Products</h4>
                    </div>

                    <div id={`carousel-related-product`}>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_08.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Red Clothing</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$20.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_09.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>White Shirt</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$25.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_10.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$45.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_11.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Black Fashion</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$60.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_08.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li className={``}>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$80.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_09.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$110.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_10.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$125.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_11.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$160.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_08.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$180.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_09.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$220.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_10.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$250.00</p>
                                </div>
                            </div>
                        </div>

                        <div className={`p-2 pb-3`}>
                            <div className={`product-wap card rounded-0`}>
                                <div className={`card rounded-0`}>
                                    <img alt={`#`} className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_11.jpg`} />
                                    <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                        <ul className={`list-unstyled`}>
                                            <li><Link className={`btn btn-success text-white`} to={`shop-single`}><i className={`far fa-heart`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`far fa-eye`}></i></Link></li>
                                            <li><Link className={`btn btn-success text-white mt-2`} to={`shop-single`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                        </ul>
                                    </div>
                                </div>
                                <div className={`card-body`}>
                                    <Link to={`shop-single.html`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
                                    <ul className={`w-100 list-unstyled d-flex justify-content-between mb-0`}>
                                        <li>M/L/X/XL</li>
                                        <li className={`pt-2`}>
                                            <span className={`product-color-dot color-dot-red float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-blue float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-black float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-light float-left rounded-circle ml-1`}></span>
                                            <span className={`product-color-dot color-dot-green float-left rounded-circle ml-1`}></span>
                                        </li>
                                    </ul>
                                    <ul className={`list-unstyled d-flex justify-content-center mb-1`}>
                                        <li>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-warning fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                            <i className={`text-muted fa fa-star`}></i>
                                        </li>
                                    </ul>
                                    <p className={`text-center mb-0`}>$300.00</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

export default Article