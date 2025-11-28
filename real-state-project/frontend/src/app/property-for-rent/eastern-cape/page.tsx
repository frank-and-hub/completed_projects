import { getBaseURl } from "@/utils/createIconUrl";
import { Metadata } from "next";
import City3AverageRentalPriceSection from "./City3AverageRentalPriceSection";
import City3BannerSection from "./City3BannerSection";
import City3FrequentlyAskedQuestionsSection from "./City3FrequentlyAskedQuestionsSection";
import City3PropertiesAvailable from "./City3PropertiesAvailable";
import City3SectionTwo from "./City3SectionTwo";
import cityListCountPrefetch from "../cityListCountPrefetch";
import { HydrationBoundary } from "@tanstack/react-query";

export const metadata: Metadata = {
  title: "Find Apartments & Houses to Rent | Property for Rent in Eastern Cape",
  description:
    "Looking for property for rent in Eastern Cape? Browse apartments, flats, and houses in East London, Gqeberha, Jeffreys Bay, and more. Find your ideal rental today!",
  alternates: {
    canonical: "https://pocketproperty.app/property-for-rent/eastern-cape/",
  },
  openGraph: {
    title: "PocketProperty in Eastern Cape",
    description: "Find Your Dream Home in Eastern Cape",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Rental Solutions in Eastern Cape",
      },
    ],
  },
};
async function page() {
  const dehydrateState = cityListCountPrefetch();

  return (
    <HydrationBoundary state={dehydrateState}>
      <City3BannerSection />
      <City3SectionTwo />
      <City3PropertiesAvailable />
      {/* <City3PopularCitiesSection /> */}
      <City3AverageRentalPriceSection />
      {/* <City3FeaturedProperties /> */}
      <City3FrequentlyAskedQuestionsSection />
    </HydrationBoundary>
  );
}

export default page;
