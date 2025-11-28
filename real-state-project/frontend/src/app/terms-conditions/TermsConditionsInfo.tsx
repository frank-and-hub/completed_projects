"use client";
import { Container, List, Title } from "@mantine/core";
import React from "react";
import "../../components/innerPageHeading/innerheading.scss";
import { getBaseURl } from "@/utils/createIconUrl";

function TermsConditionsInfo() {
  return (
    <div className="list_off_terms">
      <Container size={"lg"}>
        {/* <iframe
          src={getBaseURl() + "/assets/admin/pages/terms-and-conditions.pdf"}
          width="100%"
          height="50vh"
          style={{
            background: "#FFF",
            height: "60vh",
          }}
        /> */}
        <h3>1. Introduction</h3>
        <p>
          Welcome to PocketProperty ("the Service"). By using our Service, you
          agree to be bound by these Terms and Conditions ("Terms"). Please read
          them carefully.
        </p>
        <h3>2. Service Description</h3>
        <p>
          PocketProperty is a rental matchmaking platform that helps connect
          tenants with rental listings published directly on the PocketProperty
          platform. Users may include tenants, private landlords, and rental
          agencies (including their agents). We integrate with WhatsApp for
          communication, OneSignal for notifications, GetVerified for credit
          reports, and PayFast for payment processing in South Africa.
        </p>
        <h3>3. Eligibility</h3>
        <p>
          To use PocketProperty, you must be at least 18 years old and capable
          of entering into a legally binding agreement.
        </p>
        <h3>4. Account Registration</h3>
        <p>
          4.1 You must create an account to access certain features of
          PocketProperty.
          <br />
          4.2 You are responsible for maintaining the confidentiality of your
          account and for all activities under it.
          <br />
          4.3 You agree to provide accurate, current, and complete information
          during the registration process.
        </p>
        <h3>5. User Responsibilities</h3>
        <Title size={"md"} order={5} mt={"md"}>
          5.1 All Users:
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>Use PocketProperty only for lawful purposes.</List.Item>
          <List.Item>
            Do not post or transmit any defamatory, infringing, or unlawful
            content.
          </List.Item>
          <List.Item>Do not engage in fraudulent activity.</List.Item>
          <List.Item>
            Do not reverse engineer, decompile, or disassemble any part of the
            platform.
          </List.Item>
        </List>
        <Title size={"md"} order={5} mt={"md"}>
          5.2 Tenants:
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            Provide accurate and complete information about your rental needs.
          </List.Item>
          <List.Item>
            You are responsible for choosing who to share your credit report and
            personal documents with.
          </List.Item>
          <List.Item>
            You may be asked to consent to a credit check via GetVerified. Your
            documents and credit reports are securely stored and are never
            shared without your explicit permission.
          </List.Item>
          <List.Item>
            Matches provided are based on listings available on the
            PocketProperty platform and do not guarantee property availability
            or suitability.
          </List.Item>
        </List>
        <Title size={"md"} order={5} mt={"md"}>
          5.3 Private Landlords:
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            You may publish real, available rental properties on the
            PocketProperty platform.
          </List.Item>
          <List.Item>
            Ensure that all listings are accurate, current, and lawful.
          </List.Item>
          <List.Item>
            Only publish properties you are legally permitted to let.
          </List.Item>
          <List.Item>
            You are responsible for communication and any rental agreements made
            as a result of listings.
          </List.Item>
        </List>
        <Title size={"md"} order={5} mt={"md"}>
          5.4 Rental Agencies and Agents:{" "}
        </Title>
        <List
          ms={"xl"}
          mb={"md"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            Agencies and agents may publish multiple listings through an agency
            account.
          </List.Item>
          <List.Item>
            Each listing must represent an actual, available property.
          </List.Item>
          <List.Item>
            Agents must be authorized and compliant with South African property
            laws.
          </List.Item>
          <List.Item>
            Office IDs and agent details may be required when publishing a
            listing.
          </List.Item>
        </List>
        {/* <p>
          5.1 You agree to use PocketProperty only for lawful purposes.
          <br />
          5.2 You will not post or transmit any content that is defamatory,
          infringing, or otherwise unlawful.
          <br />
          5.3 You will not use PocketProperty to engage in any fraudulent
          activities.
          <br />
          5.4 You agree not to reverse engineer, decompile, or disassemble any
          part of PocketProperty.
          <br />
        </p> */}
        <h3>6. Property Matching</h3>
        <p>
          6.1 PocketProperty matches rental needs with properties listed on the
          PocketProperty platform.
          <br />
          6.2 Accurate information is essential for effective matching.
          <br />
          6.3 PocketProperty does not guarantee property availability or
          suitability.
          <br />
          6.4 By using PocketProperty, you consent to sharing your rental
          request or listing details with relevant parties (e.g., agents or
          landlords) for matching purposes.
        </p>
        <h3>7. Property Listings</h3>
        <p>
          7.1 All listings must comply with local laws and regulations. <br />
          7.2 No false, misleading, or outdated information may be published.
          <br />
          7.3 PocketProperty reserves the right to remove any listings that
          violate these Terms.
        </p>

        <h3>8. Credit Reports & Verification (via GetVerified)</h3>
        <p>
          8.1 PocketProperty integrates with GetVerified to enable sourcing of
          credit report for tenants.
          <br />
          8.2 By making use of this feature, you agree to{" "}
          <strong>GetVerified’s terms of service and privacy policy.</strong>
          <br />
          8.3 Tenants maintain full control over who their credit reports are
          shared with. No report is shared with a landlord or agent without
          explicit tenant consent.
          <br />
          8.4 Credit reports and personal documents are securely stored and
          encrypted.
          <br />
          <strong>Disclaimer:</strong> PocketProperty acts only as an
          intermediary for accessing credit reports via GetVerified. We do not
          alter or process credit data. Any concerns about accuracy or results
          must be directed to GetVerified.
        </p>

        <h3>9. Payments</h3>
        <p>
          9.1 Payments for premium features or listings are processed securely
          through PayFast.
        </p>
        <p>
          9.2 By using PayFast, you agree to their terms and privacy policy.
        </p>
        <h3>10. Communication</h3>
        <p>
          10.1 PocketProperty integrates with WhatsApp to send alerts and
          updates.
          <br />
          10.2 By using this feature, you agree to WhatsApp’s terms and privacy
          policy.{" "}
        </p>
        <h3>11. Notifications</h3>
        <p>
          11.1 PocketProperty uses OneSignal to send email notifications. <br />
          11.2 By using this feature, you agree to OneSignal’s terms and privacy
          policy.{" "}
        </p>
        <h3>12. Termination</h3>
        <p>
          12.1 PocketProperty may suspend or terminate user accounts at any
          time. <br />
          12.2 Upon termination, your access to the platform will immediately
          cease.
        </p>
        <h3>13. Limitation of Liability</h3>
        <p>
          13.1 PocketProperty is provided “as is” without warranties of any
          kind. <br />
          13.2 We are not liable for any direct, indirect, or consequential
          damages arising from the use of our platform or third-party services.
        </p>
        <h3>14. Changes to Terms</h3>
        <p>
          We may update these Terms at any time. Continued use of the Service
          implies your acceptance of the revised Terms.
        </p>
        <h3>15. Governing Law</h3>
        <p>
          These Terms are governed by the laws of South Africa. Any disputes
          will be subject to the jurisdiction of South African courts.{" "}
        </p>
        <h3>16. Contact Us</h3>
        <p>
          For questions or support, please contact:{" "}
          <strong>info@pocketproperty.app</strong>
        </p>

        <hr />
        <h3 />
        <h6 style={{ paddingBottom: 20 }}>
          By using PocketProperty, you agree to these Terms and conditions.
          Thank you for choosing PocketProperty.
        </h6>
      </Container>
    </div>
  );
}

export default TermsConditionsInfo;
