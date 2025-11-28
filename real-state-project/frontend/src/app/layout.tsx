import ReactQueryProvider from "@/utils/ReactQueryProvider";
import { ColorSchemeScript, MantineProvider } from "@mantine/core";
import "@mantine/core/styles.css";
import "@mantine/dropzone/styles.css";
import "@mantine/notifications/styles.css";
import { Notifications } from "@mantine/notifications";
import { Suspense } from "react";

import { Inter } from "next/font/google";
import { theme } from "../../theme";
import StoreProvider from "./StoreProvider";
import { GoogleOAuthProvider } from "@react-oauth/google";

import "./globals.scss";

import { GlobalContextProvider } from "@/utils/context";

import MantineContainer from "./mantineContainer/MantineContainer";
import { ModalsProvider } from "@mantine/modals";
import Script from "next/script";
import Link from "next/link";

const inter = Inter({ subsets: ["latin"] });

// export const metadata: Metadata = {
//   title: "PocketProperty | Matchmaker for Rentals: Your Dream Property Awaits",
//   description:
//     "PocketProperty lalala offers the ultimate rental matchmaking service, connecting tenants with their perfect rental homes. Start your search today!",
//   keywords: [
//     "PocketProperty",
//     "Rental Matchmaking Service",
//     "Find Rental Properties",
//     "Property Rental Listings",
//     "Rent a Home",
//     "Rent Apartments",
//     "Rental Property Search",
//     "Property Match Service",
//     "Rental Listings Platform",
//     "Housing Matchmaker",
//     "Real Estate Rentals",
//     "Apartment Finder",
//     "Rental Marketplace",
//     "Match Renters with Properties",
//     "Whatsapp integrated rental journey",
//     "Automated rental matching notifications on Whatsapp",
//     "Find Rental Properties in South Africa",
//   ],
// };

// export function generateMetadata(): Metadata {
//   return {
//     openGraph: {
//       title: "og tenant test",
//       description: "og description test",
//       type: "website",
//       images: [
//         {
//           url: "https://staging.pocketproperty.app/assets/admin/images/og-tenant.png", // Absolute URL for OG image
//           width: 1200,
//           height: 630,
//           alt: "testing alt",
//         },
//       ],
//     },
//   };
// }

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <head>
        <Script
          id="gtm"
          strategy="afterInteractive"
          dangerouslySetInnerHTML={{
            __html: `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-N3ND4T5L');`,
          }}
        />

        <ColorSchemeScript defaultColorScheme="light" />
        {/* <script
          src={`https://maps.googleapis.com/maps/api/js?key=${process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY}&callback=googlemapsloaded`}
          async={true}
        ></script> */}
      </head>

      <body className={inter.className}>
        <noscript>
          <iframe
            src="https://www.googletagmanager.com/ns.html?id=GTM-N3ND4T5L"
            height="0"
            width="0"
            style={{ display: "none", visibility: "hidden" }}
          ></iframe>
        </noscript>

        <GoogleOAuthProvider clientId="86244187344-uuruo5nph9lnba1rakbu7on0tdiihlu3.apps.googleusercontent.com">
          <Suspense>
            <GlobalContextProvider>
              <ReactQueryProvider>
                <StoreProvider>
                  <MantineProvider theme={theme}>
                    <Notifications position="top-right" />
                    <ModalsProvider>
                      <MantineContainer>{children}</MantineContainer>
                    </ModalsProvider>
                  </MantineProvider>
                </StoreProvider>
              </ReactQueryProvider>
            </GlobalContextProvider>
          </Suspense>
        </GoogleOAuthProvider>

        <div
          style={{
            position: "absolute",
            top: "-100%",
            opacity: 0,
            zIndex: -1,
          }}
        >
          <nav>
            <a href="/contact-us">Contact Us</a>
            <a href="/list-your-property-for-rent">List Your Property</a>
            <a href="/list-your-property-for-rent/landlords">Landlords</a>
            <a href="/list-your-property-for-rent/agency-owners">
              Agency Owners
            </a>
            <a href="/property-for-rent/gauteng">Properties in Gauteng</a>
            <a href="/property-for-rent/eastern-cape">
              Properties in Eastern Cape
            </a>
            <a href="/property-for-rent/kwazulu-natal">
              Properties in KwaZulu-Natal
            </a>
            <a href="/property-for-rent/mpumalanga">Properties in Mpumalanga</a>
            <a href="/property-for-rent/western-cape">
              Properties in Western Cape
            </a>
            <a href="/privacy-policy">Privacy Policy</a>
            <a href="/terms-conditions">Terms & Conditions</a>
            {/* Add other links as needed */}
          </nav>
          <h1>Welcome to Pocket Property</h1>
        </div>
      </body>
    </html>
  );
}
