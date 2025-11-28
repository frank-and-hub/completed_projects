"use client";

import { Center } from "@mantine/core";
import dynamic from "next/dynamic";
import Script from "next/script";
import React, { useState } from "react";

const SectionSix = dynamic(() => import("./SectionSix"), {
  ssr: false,
  loading: () => (
    <Center>
      <p>Loading Map...</p>
    </Center>
  ),
});

function MapLoadScript() {
  const [isMapLoaded, setIsMapLoaded] = useState(false);

  return (
    <>
      <Script
        id="__googleMapsScriptId"
        onLoad={() => {
          setTimeout(() => {
            setIsMapLoaded(true);
          }, 1000);
        }}
        src={`https://maps.googleapis.com/maps/api/js?key=${process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY}&libraries=places&callback=googlemapsloaded`}
        strategy="lazyOnload"
      />

      {isMapLoaded ? <SectionSix /> : null}
    </>
  );
}

export default MapLoadScript;
