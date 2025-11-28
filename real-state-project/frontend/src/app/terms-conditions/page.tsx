import React from "react";
import InnerHeading from "@/components/innerPageHeading/InnerHeading";
import TermsConditionsInfo from "./TermsConditionsInfo";
import TremImage from "../../../assets/images/trems.png";

async function TermsConditions() {
  return (
    <section className="content_terms_space">
      <InnerHeading heading="Terms and Conditions" image={TremImage} />
      <TermsConditionsInfo />
    </section>
  );
}
export function generateMetadata() {
  return {
    title: "PocketProperty | Terms and conditions",
    description:
      "Find the best rental properties effortlessly with PocketProperty. Our service matches your rental requests with available properties",
    robots: "noindex, nofollow, noarchive",
  };
}

export default TermsConditions;
