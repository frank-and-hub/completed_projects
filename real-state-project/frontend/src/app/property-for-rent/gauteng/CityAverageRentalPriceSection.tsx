import CustomText from "@/components/customText/CustomText";
import { Container, Flex } from "@mantine/core";
import Image from "next/image";
import LogoSvg from "../../../../assets/images/logo.svg";
import React from "react";
export const AverageRentalData=[{title:"Property Type",value:"Average Monthly Rent in Gauteng"},{title:"1-Bedroom Apartment",value:"R7,500  -   R12,000"},
{title:"2-Bedroom Apartment",value:"R12,000 -  R18,000"},
{title:"Townhouse",value:"R15,000  -  R25,000"},
]
function CityAverageRentalPriceSection() {
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
            <h2>average rental price In <span>Gauteng</span> </h2>
            <h3>How Much Does It Cost to Rent in Gauteng?</h3>
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
  );
}

export default CityAverageRentalPriceSection;
