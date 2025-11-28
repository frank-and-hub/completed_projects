import React from 'react'
import { Link } from 'react-router-dom'

function Product() {
    return (
        <>
            <section className={`bg-light`}>
                <div className={`container pb-5`}>
                    <div className={`row`}>
                        <div className={`col-lg-5 mt-5`}>
                            <div className={`card mb-3`}>
                                <img className={`card-img img-fluid`} src={`assets/img/product_single_10.jpg`} alt={`Card cap`} id={`product-detail`} />
                            </div>
                            <div className={`row`}>
                                <div className={`col-1 align-self-center`}>
                                    <Link to={`#multi-item-example`} role={`button`} data-bs-slide={`prev`}>
                                        <i className={`text-dark fas fa-chevron-left`}></i>
                                        <span className={`sr-only`}>Previous</span>
                                    </Link>
                                </div>
                                <div id={`multi-item-example`} className={`col-10 carousel slide carousel-multi-item`} data-bs-ride={`carousel`}>
                                    <div className={`carousel-inner product-links-wap`} role={`listbox`}>

                                        
                                        <div className={`carousel-item active`}>
                                            <div className={`row`}>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_01.jpg`} alt={`Product 1`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_02.jpg`} alt={`Product 2`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_03.jpg`} alt={`Product 3`} />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                        <div className={`carousel-item`}>
                                            <div className={`row`}>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_04.jpg`} alt={`Product 4`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_05.jpg`} alt={`Product 5`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_06.jpg`} alt={`Product 6`} />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                        <div className={`carousel-item`}>
                                            <div className={`row`}>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_07.jpg`} alt={`Product 7`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_08.jpg`} alt={`Product 8`} />
                                                    </Link>
                                                </div>
                                                <div className={`col-4`}>
                                                    <Link to={`#`}>
                                                        <img className={`card-img img-fluid`} src={`assets/img/product_single_09.jpg`} alt={`Product 9`} />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div className={`col-1 align-self-center`}>
                                    <Link to={`#multi-item-example`} role={`button`} data-bs-slide={`next`}>
                                        <i className={`text-dark fas fa-chevron-right`}></i>
                                        <span className={`sr-only`}>Next</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div className={`col-lg-7 mt-5`}>
                            <div className={`card`}>
                                <div className={`card-body`}>
                                    <h1 className={`h2`}>Active Wear</h1>
                                    <p className={`h3 py-2`}>$25.00</p>
                                    <p className={`py-2`}>
                                        <i className={`fa fa-star text-warning`}></i>
                                        <i className={`fa fa-star text-warning`}></i>
                                        <i className={`fa fa-star text-warning`}></i>
                                        <i className={`fa fa-star text-warning`}></i>
                                        <i className={`fa fa-star text-secondary`}></i>
                                        <span className={`list-inline-item text-dark`}>Rating 4.8 | 36 Comments</span>
                                    </p>
                                    <ul className={`list-inline`}>
                                        <li className={`list-inline-item`}>
                                            <h6>Brand:</h6>
                                        </li>
                                        <li className={`list-inline-item`}>
                                            <p className={`text-muted`}><strong>Easy Wear</strong></p>
                                        </li>
                                    </ul>

                                    <h6>Description:</h6>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod temp incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse. Donec condimentum elementum convallis. Nunc sed orci a diam ultrices aliquet interdum quis nulla.</p>
                                    <ul className={`list-inline`}>
                                        <li className={`list-inline-item`}>
                                            <h6>Avaliable Color :</h6>
                                        </li>
                                        <li className={`list-inline-item`}>
                                            <p className={`text-muted`}><strong>White / Black</strong></p>
                                        </li>
                                    </ul>

                                    <h6>Specification:</h6>
                                    <ul className={`list-unstyled pb-3`}>
                                        <li>Lorem ipsum dolor sit</li>
                                        <li>Amet, consectetur</li>
                                        <li>Adipiscing elit,set</li>
                                        <li>Duis aute irure</li>
                                        <li>Ut enim ad minim</li>
                                        <li>Dolore magna aliqua</li>
                                        <li>Excepteur sint</li>
                                    </ul>

                                    <form action={``} method={`GET`}>
                                        <input type={`hidden`} name={`product-title`} defaultValue={`Activewear`} />
                                        <div className={`row`}>
                                            <div className={`col-auto`}>
                                                <ul className={`list-inline pb-3`}>
                                                    <li className={`list-inline-item`}>Size :
                                                        <input type={`hidden`} name={`product-size`} id={`product-size`} defaultValue={`S`} />
                                                    </li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success btn-size`}>S</span></li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success btn-size`}>M</span></li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success btn-size`}>L</span></li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success btn-size`}>XL</span></li>
                                                </ul>
                                            </div>
                                            <div className={`col-auto`}>
                                                <ul className={`list-inline pb-3`}>
                                                    <li className={`list-inline-item text-right`}>
                                                        Quantity
                                                        <input type={`hidden`} name={`product-quanity`} id={`product-quanity`} defaultValue={`1`} />
                                                    </li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success`} id={`btn-minus`}>-</span></li>
                                                    <li className={`list-inline-item`}><span className={`badge bg-secondary`} id={`var-value`}>1</span></li>
                                                    <li className={`list-inline-item`}><span className={`btn btn-success`} id={`btn-plus`}>+</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div className={`row pb-3`}>
                                            <div className={`col d-grid`}>
                                                <button type={`submit`} className={`btn btn-success btn-lg`} name={`submit`} defaultValue={`buy`}>Buy</button>
                                            </div>
                                            <div className={`col d-grid`}>
                                                <button type={`submit`} className={`btn btn-success btn-lg`} name={`submit`} defaultValue={`addtocard`}>Add To Cart</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

export default Product