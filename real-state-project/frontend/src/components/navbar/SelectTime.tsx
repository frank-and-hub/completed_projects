import { Text } from "@mantine/core";
import React from "react";
import Image from "next/image";
import { useAppSelector } from "@/store/hooks";

interface SetTimeInner {
  selectItem: messageAlertDataType | null;
  item: messageAlertDataType;
}

function SelectTime({ item, selectItem }: SetTimeInner) {
  const { EndTime, StartTime, image, id, schedule_type } = item;
  const { userDetail } = useAppSelector((state) => state?.userReducer);

  return (
    <div
      className="select_card"
      style={{
        backgroundColor:
          selectItem?.id === id
            ? "#f30051"
            : userDetail?.schedule_type === schedule_type
            ? "#000"
            : "",
      }}
    >
      <figure>
        <Image src={image} alt="image" />
      </figure>
      <Text
        fw={700}
        style={{
          color:
            selectItem?.id === id
              ? "#ffff"
              : userDetail?.schedule_type === schedule_type
              ? "#ffff"
              : "",
        }}
      >
        {StartTime}
      </Text>
      <span
        style={{
          color:
            selectItem?.id === id
              ? "#ffff"
              : userDetail?.schedule_type === schedule_type
              ? "#ffff"
              : "",
        }}
      >
        TO
      </span>
      <Text
        fw={700}
        style={{
          color:
            selectItem?.id === id
              ? "#ffff"
              : userDetail?.schedule_type === schedule_type
              ? "#ffff"
              : "",
        }}
      >
        {EndTime}
      </Text>
    </div>
  );
}

export default SelectTime;
