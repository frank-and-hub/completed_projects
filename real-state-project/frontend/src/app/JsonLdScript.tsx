"use client";

import Script from "next/script";

export default function JsonLdScript({ schemaData }: any) {
  return (
    <Script
      id={"json-ld-script" + Math.random() + ""}
      type="application/ld+json"
      strategy="afterInteractive"
      dangerouslySetInnerHTML={{
        __html: JSON.stringify(schemaData),
      }}
      onLoad={() => console.log("JSON-LD Script loaded")}
      onError={(e) => console.error("JSON-LD Script failed to load", e)}
    />
  );
}
