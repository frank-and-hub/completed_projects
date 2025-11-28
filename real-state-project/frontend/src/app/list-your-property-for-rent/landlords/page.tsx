import React from "react";
import Home2BannerSection from "./home2/Home2BannerSection";
import Home2HowItWorkSection from "./home2/Home2HowItWorkSection";
import Home2WhyChooseUsSection from "./home2/Home2WhyChooseUsSection";
import SectionThree from "../../home/SectionThree";
import Home2LandlordsSection from "./home2/Home2LandlordsSection";
import Home2OurClientSection from "./home2/Home2OurClientSection";
import Home2FrequentlyAskedQuestionsSection from "./home2/Home2FrequentlyAskedQuestionsSection";
import Home2RentOutSection from "./home2/Home2RentOutSection";
import { Metadata } from "next";
import Home2WhoUsingSection from "./home2/Home2WhoUsingSection";
import Home2DashboardImgSection from "./home2/Home2DashboardImgSection";
import { getBaseURl } from "@/utils/createIconUrl";

import {
  dehydrate,
  HydrationBoundary,
  QueryClient,
} from "@tanstack/react-query";
import { planAmountQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { planAmount } from "@/api/plans/plan";
export const metadata: Metadata = {
  title: "List Your Property for Rent | Rent Your Apartment or House Online",
  description:
    "Looking to rent your property hassle-free? List your property online and get instant WhatsApp notifications when a verified tenant matches your listing.",

  openGraph: {
    title: "Find Tenants Easily – List Your Property for Rent",
    description:
      "List it online and instantly receive WhatsApp alerts when verified tenants match your listing.",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-agent.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "List your Property",
      },
    ],
  },
  alternates: {
    canonical:
      "https://pocketproperty.app/list-your-property-for-rent/landlords",
  },
};

async function page() {
  const queryClient = new QueryClient();

  // Prefetch data on the server
  await queryClient.prefetchQuery({
    queryKey: [...planAmountQueryKey.list],
    queryFn: planAmount,
  });
  return (
    <HydrationBoundary state={dehydrate(queryClient)}>
      <Home2BannerSection />
      <Home2HowItWorkSection />
      <Home2WhyChooseUsSection />
      <SectionThree />
      <Home2LandlordsSection />
      <Home2WhoUsingSection />
      <Home2DashboardImgSection />
      <Home2OurClientSection />
      <Home2FrequentlyAskedQuestionsSection />
      <Home2RentOutSection />
    </HydrationBoundary>
  );
}

export default page;
