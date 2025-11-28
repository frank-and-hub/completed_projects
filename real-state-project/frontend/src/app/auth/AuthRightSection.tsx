import { useGlobalContext } from "@/utils/context";
import { ActionIcon, Box, List, Text, rem } from "@mantine/core";
import React, { useMemo } from "react";
import Image from "next/image";
import rightshape from "../../../assets/images/rightshape.png";
import fileEdit from "../../../assets/svg/file_edit.svg";
import { IconHeart } from "@tabler/icons-react";
import { addSpaceInString } from "@/utils/capitalizeFiesrtLetter";

const AuthRightSection = () => {
  const {
    contextValue: {
      propertySearchData: {
        city,
        country_name,
        end_price,
        no_of_bathroom,
        no_of_bedroom,
        property_type,
        province_name,
        start_price,
        suburb_name,
        currency,
      },
      advanceFeatureSelectedData,
    },
    setIsModalOpen,
  } = useGlobalContext();

  return (
    <div className="list_of_requir">
      <Image
        src={rightshape}
        className="right_shape"
        width={200}
        height={200}
        alt="rightshape"
      />

      <h3>Your Requirements</h3>

      <ActionIcon
        className="editbtn"
        variant="default"
        onClick={() => {
          setIsModalOpen("advanceFilter");
        }}
      >
        <Image src={fileEdit} width={16} height={16} alt="fileedit" />
      </ActionIcon>
      <List>
        <List.Item>
          <span>Country:</span>
          <Text>{country_name}</Text>
        </List.Item>
        <List.Item>
          <span>Province:</span>
          <Text>{province_name}</Text>
        </List.Item>
        <List.Item>
          <span>City:</span>
          <Text>{city}</Text>
        </List.Item>
        <List.Item>
          <span>Suburb:</span>
          <Text>{suburb_name}</Text>
        </List.Item>
        <List.Item>
          <span>Property Type:</span>
          <Text>{property_type}</Text>
        </List.Item>
        <List.Item>
          <span>Bedroom:</span>
          <Text>{no_of_bedroom}</Text>
        </List.Item>
        <List.Item>
          <span>Bathroom:</span>
          <Text>{no_of_bathroom}</Text>
        </List.Item>
        <List.Item>
          <span>Minimum Price:</span>
          <Text>{`${start_price} ${currency}`}</Text>
        </List.Item>
        <List.Item>
          <span>Maximum Price:</span>
          <Text>{`${end_price} ${currency}`}</Text>
        </List.Item>
        {advanceFeatureSelectedData?.map((item, index) => {
          return (
            <div key={index} className="advance_feature_container">
              <span>{item?.title.split("_").join(" ")}:</span>
              {item?.value?.map((it, index) => {
                return (
                  <Text>
                    {addSpaceInString(it)}
                    {index === item?.value?.length - 1 ? null : ","}
                  </Text>
                );
              })}
            </div>
          );
        })}
      </List>
    </div>
  );
};

export default AuthRightSection;
