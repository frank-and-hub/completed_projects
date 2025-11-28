"use client";
import React from "react";
import JsonLdScript from "../JsonLdScript";
import FeaturedPropertiesCard from "./components/featuredPropertiesCard/FeaturedPropertiesCard";
import {
  Box,
  Button,
  Container,
  Flex,
  Grid,
  SimpleGrid,
  Text,
} from "@mantine/core";
import CustomButton from "@/components/customButton/CustomButton";
import { properties } from "../../data/properties";
import { IconArrowNarrowUp } from "@tabler/icons-react";
import Script from "next/script";
import Head from "next/head";
import { queryClient } from "@/utils/queryClient";
import { useQuery } from "@tanstack/react-query";
import { getDummyProperties } from "@/api/dummyProperties/dummyProperties";

// export async function getStaticProps() {
//   return {
//     props: { properties }, // Fetch from the local static file
//   };
// }
// const itemListSchema = {
//   "@context": "https://schema.org",
//   "@type": "ItemList",
//   itemListElement: [
//     {
//       "@type": "ListItem",
//       position: 1,
//       name: "Example Item",
//       url: "https://example.com/item",
//     },
//   ],
// };

async function FeaturedPropertiesSection() {
  const queryData = useQuery({
    queryKey: ["dummyProperties"],
    queryFn: getDummyProperties,
  });

  // const itemListSchema = {
  //   "@context": "https://schema.org",
  //   "@type": "ItemList",
  //   name: "Featured Rental Properties",
  //   itemListElement: properties.map((property, index) => ({
  //     "@type": "RealEstateListing",
  //     position: index + 1,
  //     name: property.name,
  //     url: `https://pocketproperty.com/property/${property.slug}`,
  //     image: property.image,
  //     description: property.description,
  //     address: {
  //       "@type": "PostalAddress",
  //       streetAddress: property.address.street,
  //       addressLocality: property.address.city,
  //       addressCountry: "SA",
  //     },
  //     offers: {
  //       "@type": "Offer",
  //       price: property.price,
  //       priceCurrency: "SAR",
  //       availability: "https://schema.org/InStock",
  //       businessFunction: "LeaseAction",
  //       leaseLength: {
  //         "@type": "QuantitativeValue",
  //         minValue: property.leaseLength,
  //         unitCode: "MON",
  //       },
  //     },
  //   })),
  // };
  return (
    <>
      {/* <JsonLdScript schemaData={itemListSchema} /> */}
      <section className="homeCard_sec" style={{ backgroundColor: "#FBFBFB" }}>
        <Container size={"lg"}>
          <Flex align={"center"} justify={"center"} direction="column" pb={30}>
            <Box className="heading_box_sec">
              <p>Featured Properties</p>
            </Box>
            <h2>Explore Top Rental Properties</h2>
            <h3>Check out our simple Step-by-Step Process</h3>
          </Flex>
          <SimpleGrid
            cols={{
              base: 1,
              sm: 2,
              md: 3,
            }}
            // className="simple-grid-feature-properties"
          >
            {queryData?.data?.map((property: any, index: number) => (
              <FeaturedPropertiesCard property={property} key={index} />
            ))}
          </SimpleGrid>
          <Text c={"dimmed"} mt={30} mb={20} size="xs">
            Disclaimer: This is a demo listing created to showcase the
            PocketProperty platform. We’re committed to giving equal opportunity
            to everyone who lists their properties with us—no boosted ads or
            unfair advantages. Create your account today and join the rental
            revolution with PocketProperty!
          </Text>
        </Container>
      </section>
      {/* <Head>
        <Script
          id="json-ld-script"
          type="application/ld+json"
          strategy="afterInteractive"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify(itemListSchema),
          }}
          onError={(e: Error) => {
            console.error("Script failed to load", e);
          }}
          onLoad={() => {
            console.log("Script  to load");
          }}
          strategy="afterInteractive"
        />
      </Head> */}
      {/* <Script
        id="json-ld-script"
        type="application/ld+json"
        strategy="afterInteractive"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify(itemListSchema),
        }}
        onLoad={() => console.log("JSON-LD Script loaded")}
        onError={(e) => console.error("JSON-LD Script failed to load", e)}
      />
      <section className="homeCard_sec" style={{ backgroundColor: "#FBFBFB" }}>
        <Container size={"lg"}>
          <Flex align={"center"} justify={"center"} direction="column" pb={30}>
            <Box className="heading_box_sec">
              <p>Featured Properties</p>
            </Box>
            <h2>Explore Top Rental Properties</h2>
            <h3>Check out our simple Step-by-Step Process</h3>
          </Flex>
          <SimpleGrid cols={3} className="simple-grid-feature-properties">
            {properties.map((property) => (
              <FeaturedPropertiesCard property={property} />
            ))}
          </SimpleGrid>
        </Container>
      </section> */}
    </>
  );
}

export default FeaturedPropertiesSection;
