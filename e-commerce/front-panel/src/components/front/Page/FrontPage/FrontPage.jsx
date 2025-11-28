import React from 'react'
import Carousel from '../../Carousel/Carousel'
import CategoriesofTheMonth from '../../CategoriesofTheMonth/CategoriesofTheMonth'
import FeaturedProduct from '../../FeaturedProduct/FeaturedProduct'

function FrontPage() {
    return (
        <>
            <Carousel />
            <CategoriesofTheMonth />
            <FeaturedProduct />
        </>
    );
}
export default FrontPage;