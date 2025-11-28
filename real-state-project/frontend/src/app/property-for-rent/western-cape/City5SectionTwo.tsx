"use client";
import { Container, Group } from "@mantine/core";
import CityNameBox from "../component/CityNameBox";
import useGetCityPropertyCount from "@/utils/useGetCityPropertyCount";
//import { RentalPropertiesSectionData2 } from "../gauteng/CitySectionTwo";

function City5SectionTwo() {
  const { data, isLoading } = useGetCityPropertyCount();

  const RentalPropertiesSectionData2 = [
    { id: 1, name: "Cape Town", count: data?.["cape-town"] ?? "N/A" },
    { id: 2, name: "Stellenbosch", count: data?.["stellenbosch"] ?? "N/A" },

    {
      id: 3,
      name: "Paarl",
      count: data?.["paarl"] ?? "N/A",
    },
    {
      id: 4,
      name: "George",
      count: data?.["george"] ?? "N/A",
    },
    { id: 5, name: "Somerset West", count: data?.["somerset-west"] ?? "N/A" },
  ];
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <h2 style={{ textAlign: "left" }}>
          Why Rent a Property in Western Cape?
        </h2>
        <Group mb={30} mt={30} className="city_content">
          <h4>
            The Western Cape is one of South Africa’s most desirable places to
            live, offering breathtaking coastal cities, charming wine country,
            and vibrant urban centers. Whether you're searching for apartments
            to rent in Cape Town, a flat to rent in Stellenbosch, or houses to
            rent in Paarl, this province has something for everyone. From luxury
            seaside apartments to affordable suburban flats, Western Cape
            provides excellent rental opportunities for professionals, students,
            and families.
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

export default City5SectionTwo;
