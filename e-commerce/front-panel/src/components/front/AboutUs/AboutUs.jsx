import React from 'react'

function AboutUs() {
    return (
        <>
            <section className={`bg-dark py-5`}>
                <div className={`container`}>
                    <div className={`row align-items-center py-5`}>
                        <div className={`col-md-8 text-white`}>
                            <h1>About Us</h1>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                            </p>
                        </div>
                        <div className={`col-md-4`}>
                            <img src={`assets/img/about-hero.svg`} alt={`About Hero`} loading={`lazy`} />
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

export default AboutUs