import React, { Suspense } from "react";
import PropertyDetails from "./PropertyDetail";
import { Metadata } from "next";
import { getBaseURl } from "@/utils/createIconUrl";

export const metadata: Metadata = {
  title: "Find Verified Rental Properties Across South Africa | PocketProperty",
  description:
    "Explore verified rental listings including apartments, houses, and studios across South Africa. View property details and schedule viewings easily with PocketProperty.",

  openGraph: {
    title:
      "Find Verified Rental Properties Across South Africa | PocketProperty",
    description:
      "Discover rental properties with complete details, amenities, and locations. PocketProperty makes it easy to rent verified homes and apartments across South Africa.",
    type: "website",
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "Find Rental Property in SouthAfrica",
      },
    ],
  },
  // alternates: {
  //   canonical:
  //     "https://pocketproperty.app/list-your-property-for-rent/landlords",
  // },
};

function page() {
  return (
    <>
      <Suspense>
        <PropertyDetails />
      </Suspense>
    </>
  );
}

export default page;
