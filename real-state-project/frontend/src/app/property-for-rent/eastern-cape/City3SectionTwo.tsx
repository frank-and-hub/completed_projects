"use client";
import { Container, Group } from "@mantine/core";
import CityNameBox from "../component/CityNameBox";
import useGetCityPropertyCount from "@/utils/useGetCityPropertyCount";

function City3SectionTwo() {
  const { data, isLoading } = useGetCityPropertyCount();

  const RentalPropertiesSectionData2 = [
    {
      id: 1,
      name: "Gqeberha (Port Elizabeth)",
      count: data?.["port-elizabeth"] ?? "N/A",
    },
    { id: 2, name: "East London", count: data?.["east-london"] ?? "N/A" },

    {
      id: 3,
      name: "Jeffreys Bay",
      count: data?.["jeffreys-bay"] ?? "N/A",
    },
    {
      id: 4,
      name: "Mthatha",
      count: data?.["mthatha"] ?? "N/A",
    },
    {
      id: 5,
      name: "Grahamstown (Makhanda)",
      count: data?.["Makhanda"] ?? "N/A",
    },
  ];

  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <h2 style={{ textAlign: "left" }}>
          Why Rent a Property in Eastern Cape?
        </h2>
        <Group mb={30} mt={30} className="city_content">
          <h4>
            The Eastern Cape is a province rich in coastal beauty, historic
            charm, and growing urban centers. Whether you’re looking for an
            apartment for rent in Eastern Cape, a house to rent in East London,
            or a flat to rent in Jeffreys Bay, this region offers diverse rental
            options for professionals, students, and families. From vibrant city
            living to peaceful seaside retreats, Eastern Cape has something for
            everyone.
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

export default City3SectionTwo;
