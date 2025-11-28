import React from 'react'
import { Link } from 'react-router-dom'
import Category from './Category'

function ProductList() {
    return (
        <>
            <div className={`container py-5`}>
                <div className={`row`}>
                    <Category />
                    <div className={`col-lg-9`}>
                        <div className={`row`}>
                            <div className={`col-md-6`} >
                                <ul className={`list-inline shop-top-menu pb-3 pt-1`}>
                                    <li className={`list-inline-item`}>
                                        <Link className={`h3 text-dark text-decoration-none mr-3`} to={`#`}>All</Link>
                                    </li>
                                    <li className={`list-inline-item`}>
                                        <Link className={`h3 text-dark text-decoration-none mr-3`} to={`#`}>Men's</Link>
                                    </li>
                                    <li className={`list-inline-item`}>
                                        <Link className={`h3 text-dark text-decoration-none`} to={`#`}>Women's</Link>
                                    </li>
                                </ul>
                            </div>
                            <div className={`col-md-6 pb-4`}>
                                <div className={`d-flex`}>
                                    <select className={`form-control`}>
                                        <option>Featured</option>
                                        <option>A to Z</option>
                                        <option>Item</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div className={`row`}>
                            <>
                                <div className={`col-md-4`}>
                                    <div className={`card mb-4 product-wap rounded-0`}>
                                        <div className={`card rounded-0`}>
                                            <img className={`card-img rounded-0 img-fluid`} src={`assets/img/shop_01.jpg`} alt={`#`} />
                                            <div className={`card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center`}>
                                                <ul className={`list-unstyled`}>
                                                    <li><Link className={`btn btn-success text-white`} to={`product`}><i className={`far fa-heart`}></i></Link></li>
                                                    <li><Link className={`btn btn-success text-white mt-2`} to={`product`}><i className={`far fa-eye`}></i></Link></li>
                                                    <li><Link className={`btn btn-success text-white mt-2`} to={`product`}><i className={`fas fa-cart-plus`}></i></Link></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div className={`card-body`}>
                                            <Link to={`product`} className={`h3 text-decoration-none`}>Oupidatat non</Link>
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
                            </>
                        </div>
                        <div className={`row`}>
                            <ul className={`pagination pagination-lg justify-content-end`}>
                                <li className={`page-item disabled`}>
                                    <Link className={`page-link active rounded-0 mr-3 shadow-sm border-top-0 border-left-0`} to={`#`} tabIndex={`-1`}>1</Link>
                                </li>
                                <li className={`page-item`}>
                                    <Link className={`page-link rounded-0 mr-3 shadow-sm border-top-0 border-left-0 text-dark`} to={`#`}>2</Link>
                                </li>
                                <li className={`page-item`}>
                                    <Link className={`page-link rounded-0 shadow-sm border-top-0 border-left-0 text-dark`} to={`#`}>3</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

export default ProductList