"use client";
import { Container, List, Title } from "@mantine/core";
import React from "react";
import "../../components/innerPageHeading/innerheading.scss";

function PrivacyPolicyInfo() {
  return (
    <div className="list_off_terms">
      <Container size={"lg"}>
        <h3>1. Introduction</h3>
        <p>
          PocketProperty ("the Service") is committed to protecting your
          privacy. This Privacy Policy explains how we collect, use, and share
          your information when you use our platform.
        </p>

        <h3>2. Information We Collect</h3>

        <Title size={"md"} order={5} mt={"md"}>
          2.1 Personal Information:{" "}
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            When you register on PocketProperty, we collect personal information
            such as your name, email address, phone number, and user type
            (tenant, landlord, agent).
          </List.Item>
        </List>

        <Title size={"md"} order={5} mt={"md"}>
          2.2 Usage Information:{" "}
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            We collect information about your interactions on PocketProperty,
            including the properties you list, request, or are matched to, your
            communications within the platform, and any actions related to your
            user role.
          </List.Item>
        </List>

        <Title size={"md"} order={5} mt={"md"}>
          2.3 Credit Report Information (Tenants Only):{" "}
        </Title>
        <List
          ms={"xl"}
          style={{
            listStyle: "disc",
          }}
          listStyleType="unset"
        >
          <List.Item>
            If you choose to generate a credit report through GetVerified, we
            collect and securely store the credit report and any supporting
            documents. These are only shared with rental agents or landlords if
            you give explicit consent.
          </List.Item>
        </List>

        <h3>3. How We Use Your Information</h3>
        <p>
          3.1 To provide, operate, and improve the PocketProperty platform.
          <br />
          3.2 To match tenants with listed rental properties and facilitate
          communication between all parties. <br />
          3.3 To send you notifications and updates via WhatsApp and OneSignal.{" "}
          <br />
          3.4 To process subscription or premium payments through PayFast.
          <br />
          3.5 To generate credit reports and screen applicants (tenants only,
          with consent).
        </p>

        <h3>4. Sharing Your Information</h3>
        <p>
          4.1 With PayFast for secure payment processing. <br />
          4.2 With OneSignal to send email and push notifications. <br />
          4.3 With WhatsApp to deliver real-time alerts and communication.{" "}
          <br />
          4.4 With GetVerified, if you request a credit check. By doing so, you
          agree to GetVerified’s terms and privacy policy.
          <br />
          4.5 With agents and landlords, only if you're a tenant matched to one
          of their listings, and only with your consent if it involves credit
          reports or personal documents.
          <br />
          4.6 With law enforcement or regulatory bodies when legally required.
        </p>

        <h3>5. Data Security</h3>
        <p>
          We use industry-standard security measures to protect your information
          from unauthorized access, disclosure, or misuse. Sensitive documents
          like credit reports are encrypted and access is strictly controlled.
        </p>

        <h3>6. Your Rights</h3>
        <p>
          6.1 You have the right to access, update, or delete your personal
          information.
          <br />
          6.2 You may opt out of marketing or platform communications at any
          time.
          <br />
          6.3 You control who receives access to your credit report and
          supporting documentation.
        </p>

        <h3>7. Communication</h3>
        <p>
          7.1 WhatsApp is used for platform notifications. By using this
          feature, you agree to WhatsApp’s terms and privacy policy.
          <br />
          7.2 OneSignal is used to send email or browser-based notifications.
          <br />
          7.3 Credit checks are handled by GetVerified, and by requesting one,
          you agree to their terms of service and privacy practices.
        </p>

        <h3>8. Cookies</h3>
        <p>
          We use cookies to improve your experience on PocketProperty. You can
          manage cookie settings in your browser.{" "}
        </p>

        <h3>9. Changes to This Privacy Policy</h3>
        <p>
          9.1 We may update this policy from time to time. Updates will be
          posted to our website.
          <br />
          9.2 Continued use of PocketProperty after updates means you accept the
          changes.
        </p>

        <h3>10. Termination of Service</h3>
        <p>
          If your account is suspended or terminated, your access to the
          platform will end immediately. We may retain limited information as
          required by law or for legitimate business purposes.
        </p>

        <h3>11. Governing Law</h3>
        <p>
          This Privacy Policy is governed by the laws of South Africa. Any
          disputes will be resolved in the South African courts.
        </p>
        <h3>12. Contact Us</h3>
        <p>
          If you have any questions about this Privacy Policy or your data,
          contact us at: <strong>info@pocketproperty.app</strong>
        </p>

        <hr />
        <h6 style={{ paddingBottom: 20 }}>
          By using PocketProperty, you agree to these Terms and conditions.
          Thank you for choosing PocketProperty.
        </h6>
      </Container>
    </div>
  );
}

export default PrivacyPolicyInfo;
