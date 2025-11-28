import React from "react";
import City5BannerSection from "./City5BannerSection";
import City5SectionTwo from "./City5SectionTwo";
import City5PopularCitiesSection from "./City5PopularCitiesSection";
import City5PropertiesAvailable from "./City5PropertiesAvailable";
import City5FeaturedProperties from "./City5FeaturedProperties";
import City5AverageRentalPriceSection from "./City5AverageRentalPriceSection";
import City5FrequentlyAskedQuestionsSection from "./City5FrequentlyAskedQuestionsSection";
import CityFrequentlyAskedQuestionsSection from "../gauteng/CityFrequentlyAskedQuestionsSection";
import { Metadata } from "next";
import { getBaseURl } from "@/utils/createIconUrl";
import cityListCountPrefetch from "../cityListCountPrefetch";
import { queryClient } from "@/utils/queryClient";
import { dehydrate, HydrationBoundary } from "@tanstack/react-query";
import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";

export const metadata: Metadata = {
  title: "Find Apartments & Houses to Rent | Property to Rent in Western Cape",
  description:
    "Looking for property to rent in Western Cape? Browse apartments, flats, and houses in Cape Town, Stellenbosch, Paarl, and more. Find your ideal rental home today!",

  openGraph: {
    title: "PocketProperty in WesternCape",
    description: "Find Your Dream Home in WesternCape",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Rental Solutions in WesternCape",
      },
    ],
  },
  alternates: {
    canonical: "https://pocketproperty.app/property-for-rent/western-cape",
  },
};
async function page() {
  const dehydrateState = cityListCountPrefetch();

  return (
    <HydrationBoundary state={dehydrateState}>
      <City5BannerSection />
      <City5SectionTwo />
      {/* <City5PopularCitiesSection /> */}
      <City5PropertiesAvailable />
      <City5AverageRentalPriceSection />
      {/* <City5FeaturedProperties /> */}
      <City5FrequentlyAskedQuestionsSection />
    </HydrationBoundary>
  );
}

export default page;
