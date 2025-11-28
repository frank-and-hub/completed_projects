import { Avatar, Card, CardProps, Flex, Group } from "@mantine/core";
import { IconStarFilled } from "@tabler/icons-react";
import React from "react";
interface TClientReviewCard {
  item: {
    useName: string;
    id: string | number;
    message: string;
    profileImage: string;
    location: string;
  };
  cardProps?: CardProps;
}
function ClientReviewCard({ item, cardProps }: TClientReviewCard) {
  return (
    <Card
      shadow="sm"
      padding="lg"
      radius="md"
      withBorder
      className="home-card-container"
      {...cardProps}
    >
      <Flex style={{ flexDirection: "row" }} mb={10}>
        {Array(5)
          .fill("")
          .map((_, index) => (
            <IconStarFilled key={index} size={20} color="#F7C547" />
          ))}
      </Flex>
      <Group style={{ flexDirection: "row" }} mb={20}>
        {/* <Avatar
          src={item?.profileImage}
          alt="User-Image"
          radius="xl"
          size={50}
        /> */}
        <div style={{ width: "70%" }}>
          <h3>{item?.useName}</h3>
          <h4>{item?.location}</h4>
        </div>
      </Group>
      <h4>{item?.message}</h4>
    </Card>
  );
}

export default ClientReviewCard;
