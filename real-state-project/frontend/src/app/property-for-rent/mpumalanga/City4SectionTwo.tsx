"use client";
import useGetCityPropertyCount from "@/utils/useGetCityPropertyCount";
import { Container, Group } from "@mantine/core";
import CityNameBox from "../component/CityNameBox";
//import { RentalPropertiesSectionData2 } from "../gauteng/CitySectionTwo";

function City4SectionTwo() {
  const { data, isLoading } = useGetCityPropertyCount();

  const RentalPropertiesSectionData2 = [
    {
      id: 1,
      name: "Mbombela (Nelspruit)",
      count: data?.["nelspruit"] ?? "N/A",
    },
    { id: 2, name: "White River", count: data?.["white-river"] ?? "N/A" },

    {
      id: 3,
      name: "Secunda",
      count: data?.["secunda"] ?? "N/A",
    },
    {
      id: 4,
      name: "eMalahleni (Witbank)",
      count: data?.["emalahleni"] ?? "N/A",
    },
    { id: 5, name: "Middelburg", count: data?.["middelburg"] ?? "N/A" },
  ];
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <h2 style={{ textAlign: "left" }}>
          Why Rent a Property in Mpumalanga?
        </h2>
        <Group mb={30} mt={30} className="city_content">
          <h4>
            Mpumalanga, known as the "Place of the Rising Sun," is a province
            filled with natural beauty, thriving towns, and growing economic
            hubs. Whether you're searching for houses to rent in Nelspruit,
            flats to rent in Secunda, or a flat to rent in White River,
            Mpumalanga offers a wide range of rental options for families,
            professionals, and students.
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

export default City4SectionTwo;
