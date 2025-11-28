import React from "react";
import InnerHeading from "@/components/innerPageHeading/InnerHeading";
import privacyImage from "../../../assets/images/privacy.png";
import PrivacyPolicyInfo from "./PrivacyPolicyInfo";

async function TermsConditions() {
  return (
    <section className="content_terms_space">
      <InnerHeading heading="Privacy Policy" image={privacyImage} />
      <PrivacyPolicyInfo />
    </section>
  );
}

export default TermsConditions;

export function generateMetadata() {
  return {
    title: "PocketProperty | Privacy Policy",
    description:
      "Looking for a rental home? PocketProperty makes it easy to find the perfect match between rental requests and available properties",
    robots: "noindex, nofollow",
  };
}
