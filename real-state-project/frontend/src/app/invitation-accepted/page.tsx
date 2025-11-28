import React from "react";
import "./InvitationAcceptThankyou.scss";
import InvitationAcceptThankyou from "./InvitationAcceptThankyou";
function page() {
  return (
    <>
      <InvitationAcceptThankyou />
    </>
  );
}
export function generateMetadata() {
  return {
    title: "PocketProperty | Thank You for Accepting!",
    description:
      "Your acceptance has been confirmed. Stay tuned for updates and further details from PocketProperty.",
    robots: "noindex, nofollow, noarchive",
  };
}
export default page;
