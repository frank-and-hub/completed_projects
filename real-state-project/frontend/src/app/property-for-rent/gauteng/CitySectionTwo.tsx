"use client";

import useGetCityPropertyCount from "@/utils/useGetCityPropertyCount";
import { Container, Group } from "@mantine/core";
import CityNameBox from "../component/CityNameBox";

function CitySectionTwo() {
  // const propertiesCount = queryClient.getQueryData<IObject>(["property_count"]);
  const { data, isLoading } = useGetCityPropertyCount();

  const RentalPropertiesSectionData2 = [
    { id: 1, name: "Sandton", count: data?.sandton ?? "N/A" },
    { id: 2, name: "Johannesburg", count: data?.johannesburg ?? "N/A" },

    {
      id: 3,
      name: "Pretoria",
      count: data?.pretoria ?? "N/A",
    },
    {
      id: 4,
      name: "Midrand",
      count: data?.midrand ?? "N/A",
    },
    { id: 5, name: "Krugersdorp", count: data?.krugersdorp ?? "N/A" },
  ];
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <h2 style={{ textAlign: "left" }}>Why Rent a Property in Gauteng?</h2>
        <Group mb={30} mt={30} className="city_content">
          <h4>
            Gauteng, the economic powerhouse of South Africa, offers a vibrant
            mix of urban living, business hubs, and family-friendly suburbs.
            Whether you're looking for apartments to rent in Gauteng for a
            modern city lifestyle or flats to rent in Midrand for easy
            commuting, the province has diverse rental options to suit different
            budgets and preferences.
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

export default CitySectionTwo;
