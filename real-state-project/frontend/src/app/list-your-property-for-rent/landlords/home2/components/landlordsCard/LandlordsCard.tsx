import { Card, CardProps } from "@mantine/core";
import React from "react";
import "./landlordsCard.scss";
interface TLandlordsCard {
  item: { title: string; value: string; id: string | number };
  cardProps?: CardProps;
}
function LandlordsCard({ item, cardProps }: TLandlordsCard) {
  return (
    <Card
      padding="sm"
      radius="md"
      className="landlord_card_container"
      {...cardProps}
    >
      <h3>{item?.title}</h3>
      <h4>{item?.value}</h4>
    </Card>
  );
}

export default LandlordsCard;
