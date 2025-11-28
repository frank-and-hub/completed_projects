import React from "react";
import Home3BannerSection from "./home3/Home3BannerSection";
import Home3HowItWorkSection from "./home3/Home3HowItWorkSection";
import Home3WhyChooseUsSection from "./home3/Home3WhyChooseUsSection";
import SectionThree from "../../home/SectionThree";
import Home3OurClientSection from "./home3/Home3OurClientSection";
import Home3FrequentlyAskedQuestionsSection from "./home3/Home3FrequentlyAskedQuestionsSection";
import Home3ManagingPropertiesSection from "./home3/Home3ManagingPropertiesSection";
import { Metadata } from "next";
import Home3WhoUsingSection from "./home3/Home3WhoUsingSection";
import Home3DashboardImgSection from "./home3/Home3DashboardImgSection";
import Home3DedicatedAgenciesSection from "./home3/Home3DedicatedAgenciesSection";
import { getBaseURl } from "@/utils/createIconUrl";
import {
  dehydrate,
  HydrationBoundary,
  QueryClient,
} from "@tanstack/react-query";
import { planAmountQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { planAmount } from "@/api/plans/plan";

export const metadata: Metadata = {
  title:
    "Best Rental Property Management Software for Agencies | PocketProperty",
  description:
    "Manage listings, automate tenant matching, and schedule viewings effortlessly with PocketProperty’s property management system. Get Started your agency’s rentals today!",

  openGraph: {
    title: "PocketProperty",
    description: "Best Property Management Software for Rental",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-agent.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Best Rental Managment Software",
      },
    ],
  },

  alternates: {
    canonical:
      "https://pocketproperty.app/list-your-property-for-rent/agency-owners",
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
      <Home3BannerSection />
      <Home3HowItWorkSection />
      <Home3WhyChooseUsSection />
      <SectionThree />
      <Home3DedicatedAgenciesSection />
      <Home3WhoUsingSection />
      <Home3DashboardImgSection />
      <Home3OurClientSection />
      <Home3FrequentlyAskedQuestionsSection />
      <Home3ManagingPropertiesSection />
    </HydrationBoundary>
  );
}

export default page;
