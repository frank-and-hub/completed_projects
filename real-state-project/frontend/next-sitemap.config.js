// import { getBaseURl } from "@/utils/createIconUrl";

// next-sitemap.config.js
module.exports = {
  // siteUrl: "https://staging.pocketproperty.app",
  siteUrl: "https://pocketproperty.app",
  generateRobotsTxt: true,
  exclude: [
    // 👈 clearly add this "exclude" block
    "/terms-conditions",
    "/privacy-policy",
    "/privatelandlord/login",
    "/agency/login",
    "/invitation-accepted",
    "/agent/login",
    "/portals",
    "/requests",
    "/property-detail",
    "/transaction-history",
    "/calendar-events",
    "/property-needs",
    "/api/v1/microsoft-callback",
  ],
  robotsTxtOptions: {
    policies: [
      {
        userAgent: "*",
        disallow: [
          "/terms-conditions",
          "/privacy-policy",
          "/privatelandlord/login",
          "/agency/login",
          "/invitation-accepted",
          "/agent/login",
          "/portals",
          "/requests",
          "/property-detail",
          "/transaction-history",
          "/calendar-events",
          "/property-needs",
          "/api/v1/microsoft-callback",
        ],
      },
    ],
  },
};
