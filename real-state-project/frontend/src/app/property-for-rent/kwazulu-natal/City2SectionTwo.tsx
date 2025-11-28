"use client";
import AuthModal from "@/app/auth/AuthModal";
import CustomModal from "@/components/customModal/CustomModal";
import { useAppSelector } from "@/store/hooks";
import { Box, Container, Flex, Group } from "@mantine/core";
import { IconFlag3, IconMap2 } from "@tabler/icons-react";
import Image from "next/image";
import React from "react";
import CityNameBox from "../component/CityNameBox";
import useGetCityPropertyCount from "@/utils/useGetCityPropertyCount";

// const RentalPropertiesSectionData = [
//   { id: 1, name: "Area Overview", icon: <IconMap2 stroke={2} /> },
//   { id: 2, name: "Suburbs", icon: <IconFlag3 stroke={2} /> },
//   {
//     id: 3,
//     name: "Available Properties ",
//     icon: (
//       <Image
//         src={require("../../../../assets/svg/box.svg")}
//         alt={"no_image"}
//         height={25}
//         width={25}
//       />
//     ),
//   },
//   {
//     id: 4,
//     name: "Featured Homes",
//     icon: (
//       <Image
//         src={require("../../../../assets/svg/home_with_tree.svg")}
//         alt={"no_image"}
//         height={25}
//         width={25}
//       />
//     ),
//   },
// ];

function City2SectionTwo() {
  const { data, isLoading } = useGetCityPropertyCount();

  const RentalPropertiesSectionData2 = [
    { id: 1, name: "Durban", count: data?.["durban"] ?? "N/A" },
    {
      id: 2,
      name: "Pietermaritzburg",
      count: data?.["pietermaritzburg"] ?? "N/A",
    },

    {
      id: 3,
      name: "Ballito",
      count: data?.["ballito"] ?? "N/A",
    },
    {
      id: 4,
      name: "Richards Bay",
      count: data?.["richards-bay"] ?? "N/A",
    },
    { id: 5, name: "Hillcrest", count: data?.["hillcrest"] ?? "N/A" },
  ];
  const token = useAppSelector((state) => state?.userReducer?.token);
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <h2 style={{ textAlign: "left" }}>
          Why Rent a Property in KwaZulu-Natal?
        </h2>
        <Group mb={30} mt={30} className="city_content">
          <h4>
            KwaZulu-Natal (KZN) is one of South Africa’s most sought-after
            provinces, offering a mix of coastal city living, tranquil suburbs,
            and thriving business hubs. Whether you’re looking for apartments to
            rent in Durban, houses to rent in Ballito, or flats to rent in
            Richards Bay, KZN has diverse rental options for professionals,
            families, and students alike.
          </h4>
        </Group>
        <h2 style={{ textAlign: "left" }}>Top Cities for Rent</h2>
        <Group mt={30}>
          {RentalPropertiesSectionData2.map((item, index) => (
            <CityNameBox item={item} key={index} isLoading={isLoading} />
          ))}
        </Group>
      </Container>
    </section>
  );
}

export default City2SectionTwo;
