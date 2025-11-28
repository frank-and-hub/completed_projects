import { getBaseURl } from "@/utils/createIconUrl";
import { Metadata } from "next";
import City4AverageRentalPriceSection from "./City4AverageRentalPriceSection";
import City4BannerSection from "./City4BannerSection";
import City4FrequentlyAskedQuestionsSection from "./City4FrequentlyAskedQuestionsSection";
import City4PropertiesAvailable from "./City4PropertiesAvailable";
import City4SectionTwo from "./City4SectionTwo";
import cityListCountPrefetch from "../cityListCountPrefetch";
import { HydrationBoundary } from "@tanstack/react-query";

export const metadata: Metadata = {
  title: "Find Apartments & Houses for Rent | Property for Rent in Mpumalanga",
  description:
    "Looking for property for rent in Mpumalanga? Browse houses in Nelspruit, flats in Secunda, Middelburg, and Witbank. Find affordable rentals in top locations today!",

  openGraph: {
    title: "PocketProperty in Mpumalanga",
    description: "Find Your Dream Home in Mpumalanga",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Rental Solutions in Mpumalanga",
      },
    ],
  },
  alternates: {
    canonical: "https://pocketproperty.app/property-for-rent/mpumalanga",
  },
};

async function page() {
  const dehydrateState = cityListCountPrefetch();

  return (
    <HydrationBoundary state={dehydrateState}>
      <City4BannerSection />
      <City4SectionTwo />
      {/* <City4PopularCitiesSection /> */}
      <City4PropertiesAvailable />
      <City4AverageRentalPriceSection />
      {/* <City4FeaturedProperties /> */}
      <City4FrequentlyAskedQuestionsSection />
    </HydrationBoundary>
  );
}

export default page;
