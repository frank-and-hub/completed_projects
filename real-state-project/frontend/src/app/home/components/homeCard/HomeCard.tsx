import React, { ReactNode } from "react";
import "./homeCard.scss";
import { Card, Text, Group, GroupProps, CardProps, Flex } from "@mantine/core";
import Link from "next/link";
import CustomButton from "@/components/customButton/CustomButton";

function HomeCard({
  item,
  isShowFigures,
  groupStyle,
  cardProps,
}: {
  item: {
    id: string;
    title: string;
    description: string;
    points?: string[];
    icon: ReactNode;
    link?: string;
  };
  isShowFigures?: boolean;
  groupStyle?: GroupProps;
  cardProps?: CardProps;
}) {
  return (
    <Card
      shadow="sm"
      padding="lg"
      radius="md"
      withBorder
      className="home-card-container"
      {...cardProps}
    >
      {isShowFigures && (
        <figure>
          <Text fz={13}>STEP {item?.id}</Text>
        </figure>
      )}

      <Group mt="md" mb="xs" {...groupStyle}>
        <span>{item?.icon}</span>
        <h3>{item?.title}</h3>
        <h4>{item?.description}</h4>
      </Group>

      {item?.link ? (
        <Link href={item?.link} style={{ flex: 1 }} target="_blank">
          <CustomButton>Start Creating Properties</CustomButton>
        </Link>
      ) : null}
      {/* <List>
        {item?.points?.map((row, index) => (
          <List.Item key={index}>{row}</List.Item>
        ))}
       
      </List> */}
    </Card>
  );
}

export default HomeCard;
