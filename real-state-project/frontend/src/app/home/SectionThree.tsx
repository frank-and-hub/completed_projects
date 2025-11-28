"use client";
import React, { useState } from "react";
import MemberShipCard from "./components/memberShipCard/MemberShipCard";
import { Container } from "@mantine/core";
import CustomText from "@/components/customText/CustomText";
import { usePathname } from "next/navigation";

function SectionThree() {
  const pathname = usePathname();
  const [planType, setPlanType] = useState<
    "tenant" | "privatelandlord" | "agency"
  >("tenant");
  return (
    <section id="plans" className="membership_sec">
      <Container size={"lg"}>
        <div className="title_card_sc">
          <h2>Membership Pricing</h2>
          <h3>Unlocking your benefits with our subscription</h3>
          {/* <div className="plan_button_container">
            <div
              onClick={() => {
                setPlanType("privatelandlord");
              }}
              className={
                !(planType === "privatelandlord")
                  ? "simple_plan_button"
                  : "plan_button"
              }
            >
              <span>Private Landlord</span>
            </div>
            <div
              onClick={() => {
                setPlanType("tenant");
              }}
              className={
                !(planType === "tenant") ? "simple_plan_button" : "plan_button"
              }
            >
              <span>Tenant</span>
            </div>
            <div
              onClick={() => {
                setPlanType("agency");
              }}
              className={
                !(planType === "agency") ? "simple_plan_button" : "plan_button"
              }
            >
              <span>Agency</span>
            </div>
          </div> */}
        </div>
        <MemberShipCard
          isFromSearchFilter={false}
          planType={
            pathname === "/list-your-property-for-rent/agency-owners"
              ? "agency"
              : pathname === "/list-your-property-for-rent/landlords"
              ? "privatelandlord"
              : "tenant"
          }
        />
      </Container>
    </section>
  );
}

export default SectionThree;
