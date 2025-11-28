import React from "react";
import City2BannerSection from "./City2BannerSection";
import City2SectionTwo from "./City2SectionTwo";
import City2PopularCitiesSection from "./City2PopularCitiesSection";
import City2PropertiesAvailable from "./City2PropertiesAvailable";
import City2FeaturedProperties from "./City2FeaturedProperties";
import City2AverageRentalPriceSection from "./City2AverageRentalPriceSection";
import City2FrequentlyAskedQuestionsSection from "./City2FrequentlyAskedQuestionsSection";
import CityFrequentlyAskedQuestionsSection from "../gauteng/CityFrequentlyAskedQuestionsSection";
import { Metadata } from "next";
import { getBaseURl } from "@/utils/createIconUrl";
import cityListCountPrefetch from "../cityListCountPrefetch";
import { queryClient } from "@/utils/queryClient";
import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import { dehydrate, HydrationBoundary } from "@tanstack/react-query";

export const metadata: Metadata = {
  title: "Find Your Perfect Home | Apartments & Flats to Rent in KwaZulu-Natal",
  description:
    "Looking for property to rent in KwaZulu-Natal? Browse apartments, flats, and houses in Durban, Ballito, Richards Bay, and more. Find your perfect rental home today!",

  openGraph: {
    title: "PocketProperty in KwaZulu-Natal",
    description: "Find Your Dream Home in KwaZulu-Natal",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Rental Solutions in KwaZulu-Natal",
      },
    ],
  },

  alternates: {
    canonical: "https://pocketproperty.app/property-for-rent/kwazulu-natal",
  },
};
async function page() {
  const dehydrateState = cityListCountPrefetch();

  return (
    <HydrationBoundary state={dehydrateState}>
      <City2BannerSection />
      <City2SectionTwo />
      {/* <City2PopularCitiesSection /> */}
      <City2PropertiesAvailable />
      <City2AverageRentalPriceSection />
      <City2FrequentlyAskedQuestionsSection />
      {/* <City2FeaturedProperties /> */}
    </HydrationBoundary>
  );
}

export default page;
