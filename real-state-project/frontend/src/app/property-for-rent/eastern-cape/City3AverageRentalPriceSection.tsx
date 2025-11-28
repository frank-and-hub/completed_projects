import CustomText from '@/components/customText/CustomText'
import { Container, Flex } from '@mantine/core'
import Image from 'next/image'
import React from 'react'
import LogoSvg from "../../../../assets/images/logo.svg";
import { AverageRentalData } from '../gauteng/CityAverageRentalPriceSection'

function City3AverageRentalPriceSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="average_rental_prices_container">
        <Image
        className="logoImage"
            src={LogoSvg}
            width={150}
            height={200}
            alt="PocketProperty - Find Properties to Rent"
          />
            <Flex align={'center'} justify={'center'} direction={'column'}>
            <h2>average rental price In <span>Eastern Cape</span> </h2>
            <h3>How Much Does It Cost to Rent in Eastern Cape?</h3>
            <div className="average_rental_prices_box">
                {AverageRentalData.map((ele,i)=> <Flex key={i} align={'center'} justify={'space-between'}  py={15} className="table_row">
                <CustomText ml={20}>{ele?.title}</CustomText>
                <CustomText mr={20}>{ele?.value}</CustomText>
            </Flex>)}
           
            </div>
            </Flex>
           
           
        </div>
      </Container>
    </section>
  )
}

export default City3AverageRentalPriceSection