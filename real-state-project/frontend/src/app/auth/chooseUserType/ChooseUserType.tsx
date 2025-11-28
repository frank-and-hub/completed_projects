import {
  Box,
  Card,
  Center,
  Flex,
  Image,
  SimpleGrid,
  Title,
} from "@mantine/core";
import React, { useState } from "react";
import "./ChooseUserType.style.scss";
import CustomText from "@/components/customText/CustomText";
import CustomButton from "@/components/customButton/CustomButton";
import { useRouter } from "next/router";
import { getBaseURl } from "@/utils/createIconUrl";
function ChooseUserType({ changeScreenType }: changeScreenType) {
  const [selectedType, setSelectedType] = useState<
    "tenant" | "landlord" | "agent" | "agencyOwner"
  >();
  const onChooseUserType = (
    value: "tenant" | "landlord" | "agent" | "agencyOwner"
  ) => {
    setSelectedType(value);
  };

  const onNavigate = () => {
    if (selectedType === "tenant") {
      changeScreenType("login");
    } else {
      window.open(
        getBaseURl() +
          `/${
            selectedType === "agencyOwner"
              ? "agency"
              : selectedType === "agent"
              ? "agent"
              : "privatelandlord"
          }/login`,

        "_blank"
      );
    }
  };
  return (
    <Flex direction={"column"}>
      <SimpleGrid
        cols={{ base: 1, sm: 2, md: 2 }}
        flex={1}
        className="choose-userType-container"
      >
        <Center>
          <Box
            className={`choose-card-type ${
              selectedType === "tenant" ? "active" : ""
            }`}
            onClick={() => onChooseUserType("tenant")}
          >
            <Image
              src={getBaseURl() + "/assets/admin/icon/tenant.png"}
              alt="PocketProperty - Tenant"
            />
            <Title order={5} className="title">
              Tenant
            </Title>
          </Box>
        </Center>
        <Center>
          <Box
            className={`choose-card-type ${
              selectedType === "landlord" ? "active" : ""
            }`}
            onClick={() => onChooseUserType("landlord")}
          >
            <Image
              src={getBaseURl() + "/assets/admin/icon/landlord.png"}
              alt="PocketProperty - Landlord"
            />

            <Title order={5} className="title">
              Landlord
            </Title>
          </Box>
        </Center>
        <Center>
          <Box
            className={`choose-card-type ${
              selectedType === "agent" ? "active" : ""
            }`}
            onClick={() => onChooseUserType("agent")}
          >
            <Image
              src={getBaseURl() + "/assets/admin/icon/agent.png"}
              alt="PocketProperty - AgencyOwner"
            />
            <Title order={5} className="title">
              Agent
            </Title>
          </Box>
        </Center>
        <Center>
          <Box
            className={`choose-card-type ${
              selectedType === "agencyOwner" ? "active" : ""
            }`}
            onClick={() => onChooseUserType("agencyOwner")}
          >
            <Image
              src={getBaseURl() + "/assets/admin/icon/agency.png"}
              alt="PocketProperty - AgencyOwner"
            />
            <Title order={5} className="title">
              Agency Owner
            </Title>
          </Box>
        </Center>
      </SimpleGrid>
      <Center my="md">
        <CustomButton w={"100%"} onClick={onNavigate}>
          Next
        </CustomButton>
      </Center>
    </Flex>
  );
}

export default ChooseUserType;
