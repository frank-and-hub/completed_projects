import CustomText from "@/components/customText/CustomText";
import { Flex, SimpleGrid, Title } from "@mantine/core";
import React from "react";
const WhoUsingData = [
  { count: "200", title: "Landlords actively using our platform" },
  { count: "1,700", title: "Properties listed through PocketProperty" },
  { count: "500", title: "Tenants matched with rental homes from landlords" },
];
function Home2WhoUsingSection() {
  return (
    <section className="homeCard_sec">
      <div className="who_using_container">
        <div className="who_using_content_container">
          <Flex align={"center"} justify={"center"}>
            <div className="content">
              <h2>Who’s Using Pocketproperty</h2>
              <h3>
                Many landlords save time and energy each month by trusting us to
                manage there properties{" "}
              </h3>
            </div>
          </Flex>

          <SimpleGrid cols={{ base: 2, md: 3 }} mt={20} spacing="xl">
            {WhoUsingData.map((ele, i) => (
              <div key={i}>
                <h2 style={{ fontWeight: 800 }}>{ele?.count}+</h2>
                <h3 style={{ color: "#f30051" }}>{ele?.title}</h3>
              </div>
            ))}
          </SimpleGrid>
          <Title order={5} size="xs" ta={"center"} mt={"lg"}>
            *These are ambitious milestones we're building towards — and we're
            making significant strides every month.
          </Title>
        </div>
      </div>
    </section>
  );
}

export default Home2WhoUsingSection;
