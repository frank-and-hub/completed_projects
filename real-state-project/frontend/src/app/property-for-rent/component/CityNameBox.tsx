"use client";
import AuthModal from "@/app/auth/AuthModal";
import CustomModal from "@/components/customModal/CustomModal";
import { useAppSelector } from "@/store/hooks";
import { Box, Flex, Loader } from "@mantine/core";
import React from "react";

function CityNameBox({ item, isLoading }: { item: any; isLoading?: boolean }) {
  const token = useAppSelector((state) => state.userReducer.token);
  return (
    <CustomModal
      disabled={!!token}
      actionButton={
        <Flex
          className="rental_properties_card"
          style={{
            cursor: !!!token ? "pointer" : "default",
          }}
        >
          <h3>{item?.name}</h3>
          <Box className="rental_properties_card_icon">
            {isLoading ? <Loader size={"xs"} /> : item?.count}
          </Box>
        </Flex>
      }
    >
      <AuthModal type="chooseUserType" />
    </CustomModal>
  );
}

export default CityNameBox;
