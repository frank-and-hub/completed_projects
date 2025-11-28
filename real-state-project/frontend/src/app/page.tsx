import React from "react";
import SectionOne from "./home/SectionOne";
import SectionTwo from "./home/SectionTwo";
import FeaturedPropertiesSection from "./home/FeaturedPropertiesSection";
import SectionThree from "./home/SectionThree";
import FrequentlyAskedQuestionsSection from "./home/FrequentlyAskedQuestionsSection";
import dynamic from "next/dynamic";
import type { Metadata } from "next";
import WhoWeAreSection from "./home/WhoWeAreSection";
import RentalPropertiesSection from "./home/RentalPropertiesSection";
import FindingApartment from "./home/FindingApartment";
import RenderHook from "./RenderHook";
import { properties } from "../data/properties";
import Head from "next/head";
import Image from "next/image";
import RenderFeaturedPropertiesSection from "./home/RenderFeaturedPropertiesSection";
import { getBaseURl } from "@/utils/createIconUrl";
import { planAmount } from "@/api/plans/plan";
import {
  dehydrate,
  HydrationBoundary,
  QueryClient,
} from "@tanstack/react-query";
import { planAmountQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { getDummyProperties } from "@/api/dummyProperties/dummyProperties";
import { queryClient } from "@/utils/queryClient";
import JsonLdScript from "./JsonLdScript";
import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import cityListCountPrefetch from "./property-for-rent/cityListCountPrefetch";
import { Center } from "@mantine/core";
import Home2OurClientSection from "./list-your-property-for-rent/landlords/home2/Home2OurClientSection";
//import time_icon9 from '../../../public/time_icon9.svg';
//import og-tenant.png from "@/"
//import opengraph from "../../../public/opengraph-image.png";
//import open from "../"

const Map = dynamic(() => import("./home/section6/MapLoadScript"), {
  ssr: false, // Disable server-side rendering for Google Maps
  loading: () => (
    <Center>
      <p>Loading Map...</p>
    </Center>
  ),
});

// export const metadata: Metadata = {
//   title: "Find Properties to rent | Apartments to rent in South Africa",
//   description:
//     "Search for properties to rent in South Africa. Find private property, apartments, houses, and studio apartments for rent. Start your rental search today!",

//   openGraph: {
//     title: "og tenant testtttttt",
//     description: "og description test",
//     type: "website",
//     images: [
//       {
//         url: "https://7008-49-36-237-104.ngrok-free.app/opengraph-image.png",
//         width: 1200,
//         height: 630,
//         alt: "testing alt",
//       },
//     ],
//   },
// };

export function generateMetadata(): Metadata {
  return {
    title: "Find Properties for rent | Apartments to rent in South Africa",
    description:
      "Search for properties to rent in South Africa. Find private property, apartments, houses, and studio apartments for rent. Start your rental search today!",

    openGraph: {
      title: "Find Properties for rent in South Africa!",
      description:
        "Search for properties to rent in South Africa. Find private property, apartments, houses, and studio apartments for rent. Start your rental search today!",
      type: "website",
      url: getBaseURl(),
      images: [
        {
          url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
          width: 1200,
          height: 630,
          alt: "Properties for rent in SouthAfrica",
        },
      ],
    },
    alternates: {
      canonical: "https://pocketproperty.app/",
    },
  };
}
async function page() {
  // Prefetch data on the server
  await queryClient.prefetchQuery({
    queryKey: [...planAmountQueryKey.list],
    queryFn: planAmount,
  });
  await queryClient.prefetchQuery({
    queryKey: ["dummyProperties"],
    queryFn: getDummyProperties,
  });
  // const cityListSchema = await cityListCountPrefetch();
  // const dummyProperties = queryClient.getQueryData<propertyDetailItemType[]>([
  //   "dummyProperties",
  // ]);

  // const itemListSchema = {
  //   "@context": "https://schema.org",
  //   "@type": "ItemList",
  //   name: "Featured Rental Properties",
  //   itemListElement: dummyProperties?.map((property, index) => ({
  //     "@type": "RealEstateListing",
  //     position: index + 1,
  //     name: property.title,
  //     url: `https://pocketproperty.com/property/${slugGenerator(
  //       property?.title
  //     )}`,
  //     image: property.photos[0],
  //     description: property.description,
  //     address: {
  //       "@type": "PostalAddress",
  //       streetAddress: property.address,
  //       addressLocality: property.town,
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
  //         minValue: property.price,
  //         unitCode: "MON",
  //       },
  //     },
  //   })),
  // };

  return (
    <>
      {/* <JsonLdScript schemaData={itemListSchema} />
      <JsonLdScript schemaData={cityListSchema} /> */}

      <HydrationBoundary state={dehydrate(queryClient)}>
        <Head>
          <link rel="icon" href="/favicon.ico" />

          {/* <link rel="preload" href="/media/header_banner.png" as="image"></link> */}
        </Head>
        <RenderHook />
        <SectionOne />
        <SectionTwo />
        <RenderFeaturedPropertiesSection />
        <WhoWeAreSection />
        <RentalPropertiesSection />
        <SectionThree />
        <Map />
        <Home2OurClientSection isTenants={true} reviews={data} />
        <FrequentlyAskedQuestionsSection />
        <FindingApartment />
      </HydrationBoundary>
    </>
  );
}

export default page;

const slugGenerator = (str: string) => {
  return !str
    ? ""
    : str
        .toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w-]+/g, "");
};

const data = [
  {
    id: 1,
    useName: "Lebo Khumalo",
    location: "Pretoria, Gauteng",
    message: `I didn’t even have to search. I said what I needed, and the matches just arrived. No app,
no stress, just WhatsApp.`,
  },
  {
    id: 2,
    useName: "Imran Patel",
    location: "Sandton, Gauteng",
    message: `I didn’t have time to scroll listings all day. I said what I needed, and the right ones just came
through. One even had a viewing the next day.`,
  },
  {
    id: 1,
    useName: "Pranav Naidoo",
    location: "Durban, KwaZulu-Natal",
    message: `PocketProperty made me feel like I had an agent working for me but faster. Got
matched, viewed, moved. Done.`,
  },
  {
    id: 1,
    useName: "Johan van der Merwe",
    location: "Cape Town, Western Cape",
    message: `Other sites make you work. This one just… works. It knew what I wanted. I didn’t lift a
finger except to reply on WhatsApp.`,
  },
];
