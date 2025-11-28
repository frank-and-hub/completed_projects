import React from "react";
import { SimpleGrid } from "@mantine/core";
import { Container } from "@mantine/core";
import Image from "next/image";

interface THeaderType {
  image: any;
  title: string;
  description?: string;
}

function PropertyHeader({ description, image, title }: THeaderType) {
  return (
    <div className="inner_header_pages">
      <Container size={"lg"}>
        <SimpleGrid cols={2} spacing="sm" verticalSpacing="sm">
          <div className="property_header">
            <h1>{title}</h1>
            <p>{description}</p>
          </div>
          <div className="property_header_img">
            <Image src={image} alt="no-image" />
          </div>
        </SimpleGrid>
      </Container>
    </div>
  );
}

export default PropertyHeader;
