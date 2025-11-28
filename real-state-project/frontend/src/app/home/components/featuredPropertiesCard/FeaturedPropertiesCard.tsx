import {
  Anchor,
  Badge,
  Button,
  Card,
  Flex,
  Group,
  Image,
  Text,
  Title,
} from "@mantine/core";
import React from "react";
import { properties } from "../../../../data/properties";
import "./FeaturedPropertiesCard.scss";
import Head from "next/head";
import {
  IconBath,
  IconBed,
  IconMapPin,
  IconMapPinFilled,
} from "@tabler/icons-react";
import Link from "next/link";
// import Image from "next/image";
//console.log(properties);

// export async function getStaticProps() {
//   return {
//     props: { properties }, // Fetch from the local static file
//   };
// }

type ComponentProps = {
  property: propertyDetailItemType;
};

const FeaturedPropertiesCard: React.FC<ComponentProps> = ({ property }) => {
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
      {/* <Head>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(itemListSchema) }}
        />
      </Head> */}
      <Card
        component="a"
        href={`/property-detail?property_id=${property?.id}&updateKey=dummy`}
        target="_blank"
        shadow="sm"
        // padding="lg"
        radius="md"
        withBorder
        className="featured_properties_card"
        display={"block"}
      >
        <Image
          src={(property?.photos?.[0] ?? "") as string}
          // height={100}
          // width={100}
          alt="Norway"
          className="featured_properties_image"
        />

        <Flex direction={"row"} align={"center"}>
          <IconMapPinFilled stroke={2} color="#f30051" />{" "}
          <Text ml={5} mt={3}>
            {property.town}
          </Text>
        </Flex>
        <Group justify="space-between" mt={10} mb={5}>
          <Link
            href={`/property-detail?property_id=${property?.id}&updateKey=dummy`}
            target="_blank"
          >
            <Title
              className="featured_properties_title"
              order={3}
              lineClamp={2}
            >
              {property?.title}
            </Title>
          </Link>

          <Text fw={600}>R {property?.price}</Text>
        </Group>
        <Text size="sm" c="#888888" lineClamp={3}>
          {property?.description}
        </Text>
        <Flex direction={"row"} align={"center"} mt={7}>
          <IconBed stroke={2} size={20} color="#888888" />{" "}
          <Text size="sm" c="#888888" ml={4}>
            {property?.beds} Beds
          </Text>
          <div className="dividerLine" />
          <IconBath stroke={2} size={15} color="#888888" />
          <Text size="sm" c="#888888" ml={4}>
            {property?.baths} Baths
          </Text>
        </Flex>
      </Card>
    </>
  );
};

export default FeaturedPropertiesCard;
