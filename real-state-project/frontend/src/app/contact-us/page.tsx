import React from "react";
import SectionFive from "../home/SectionFive";
import { Metadata } from "next";
import { getBaseURl } from "@/utils/createIconUrl";

export const metadata: Metadata = {
  title: "Contact Us | Get in Touch with PocketProperty",
  description:
    "Have questions or need assistance? Contact PocketProperty today! Our team is here to help with property management solutions for agencies, landlords or tenants. ",

  openGraph: {
    title: "PocketProperty",
    description: "Find your Dream Home | List your Property Online for Rent",
    type: "website",
    url: getBaseURl(),
    images: [
      {
        url: getBaseURl() + "/assets/admin/images/og-tenant.png", // Absolute URL for OG image
        width: 1200,
        height: 630,
        alt: "List your Property",
      },
    ],
  },
  alternates: {
    canonical: "https://pocketproperty.app/contact-us",
  },
};

// export async function getStaticProps() {
//   return {
//     props: {
//       title: "About PocketPRoperty",
//       description: "We help you find the best properties quickly and easily.",
//     },
//   };
// }

// Receive props from getStaticProps
export default function page() {
  const title = "hello";
  const description = "hello description";
  return (
    <div style={{ paddingTop: "60px" }}>
      <SectionFive />
    </div>
  );
}

//export default page;
