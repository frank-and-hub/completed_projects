import React from "react";
import CityBannerSection from "./CityBannerSection";
import CitySectionTwo from "./CitySectionTwo";
import CityPopularCitiesSection from "./CityPopularCitiesSection";
import CityPropertiesAvailable from "./CityPropertiesAvailable";
import CityFeaturedProperties from "./CityFeaturedProperties";
import CityAverageRentalPriceSection from "./CityAverageRentalPriceSection";
import { Metadata } from "next";
import CityFrequentlyAskedQuestionsSection from "./CityFrequentlyAskedQuestionsSection";
import { getBaseURl } from "@/utils/createIconUrl";
import { queryClient } from "@/utils/queryClient";
import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import cityListCountPrefetch from "../cityListCountPrefetch";
import JsonLdScript from "@/app/JsonLdScript";
import { HydrationBoundary } from "@tanstack/react-query";

export const metadata: Metadata = {
  title: "Find Your Perfect Home | Apartments & Flats to Rent in Gauteng",
  description:
    "Looking for property to rent in Gauteng? Explore a wide range of apartments, flats, and 1-bedroom rentals in Midrand, Randburg, Johannesburg, and more through PocketProperty!",

  openGraph: {
    title: "PocketProperty in Guateng",
    description: "Find Your Dream Home in Guateng",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Rental Solutions in Guateng",
      },
    ],
  },
  alternates: {
    canonical: "https://pocketproperty.app/property-for-rent/gauteng",
  },
};

async function page() {
  const dehydrateState = cityListCountPrefetch();

  return (
    <HydrationBoundary state={dehydrateState}>
      <CityBannerSection />
      <CitySectionTwo />
      <CityPropertiesAvailable />
      <CityAverageRentalPriceSection />
      {/* <CityPopularCitiesSection /> */}
      {/* <CityFeaturedProperties /> */}
      <CityFrequentlyAskedQuestionsSection />
    </HydrationBoundary>
  );
}

export default page;
