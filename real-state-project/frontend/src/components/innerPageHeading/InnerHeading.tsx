'use client';
import { Container } from '@mantine/core';
import React from 'react';
import './innerheading.scss';
import Image from 'next/image';

interface IHeaderInner {
  heading: string;
  image: any;
}

function InnerHeading({ heading, image }: IHeaderInner) {
  return (
    <div className="innner_heading_text">
      <Container size={'lg'}>
        <h1>{heading}</h1>
        {/* <Image src={image} alt="image" /> */}
      </Container>
    </div>
  );
}

export default InnerHeading;
