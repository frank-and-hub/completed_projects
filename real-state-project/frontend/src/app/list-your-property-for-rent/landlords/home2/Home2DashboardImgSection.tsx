import { Center } from "@mantine/core";
import Image from "next/image";
import React from "react";

function Home2DashboardImgSection() {
  return (
    <section className="homeCard_sec">
      <Center>
        <Image
          src={require("../../../../../assets/images/dashboard.png")}
          alt="PocketProperty Dashboard"
          width={800}
        />
      </Center>
    </section>
  );
}

export default Home2DashboardImgSection;
