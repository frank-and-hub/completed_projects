"use client";
import { getThankyouDetails } from "@/api/auth/profile";
import { showDate } from "@/utils/hasDateTimePassed";
import { Center, Flex, Loader, Paper, Table, Text, Title } from "@mantine/core";
import { useViewportSize } from "@mantine/hooks";
import { useQuery } from "@tanstack/react-query";
import dayjs from "dayjs";
import Image from "next/image";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import React, { useMemo } from "react";
import Confetti from "react-confetti";
const { Tbody, Td, Th, Thead, Tr } = Table;
function InvitationAcceptThankyou() {
  const { height, width } = useViewportSize();

  const searchParams = useSearchParams();
  const id = searchParams.get("id");
  console.log(id);

  const {
    data: propertyData,
    isLoading,
    isError,
  } = useQuery({
    queryKey: ["thank_you", id],
    queryFn: () => getThankyouDetails(id as string),
    enabled: !!id,
  });

  const data = useMemo(
    () => [
      { title: "⁠Property Name", value: propertyData?.property?.name },
      {
        title: "Property Address",
        value: propertyData?.property?.address || "-",
      },
      {
        title: " ⁠Property Viewing Date",
        value: propertyData?.pvr_date ? showDate(propertyData?.pvr_date) : "-",
      },
      {
        title: "Time",
        value: propertyData?.pvr_date
          ? dayjs(propertyData?.pvr_date).format("hh:mm A")
          : "-",
      },
      {
        title: " ⁠Landlord Name",
        value: propertyData?.property_user_name ?? propertyData?.client?.name,
      },
    ],
    [propertyData]
  );

  return (
    <section className="main_section ">
      <Confetti
        width={width}
        height={height}
        colors={[
          "#f54336",
          "#e91e63",
          "#9c27b0",
          "#673ab7",
          "#3f51b5",
          "#2196f3",
          "#03a9f4",
          "#00bcd4",
          "#009688",
          "#4CAF50",
          "#8BC34A",
          "#CDDC39",
          "#FFEB3B",
          "#FFC107",
          "#FF9800",
          "#FF5722",
          "#795548",
          "#f30051",
        ]}
        recycle={false}
        numberOfPieces={200} // Adjust for more/less confetti
      />

      <Paper className="content_box" shadow="md" radius={"md"}>
        <Flex direction={"column"} align={"center"} justify={"center"} mb={20}>
          <Image
            src={require("../../../assets/images/thankyou_img.jpeg")}
            alt="no_image"
            width={400}
          />
          <Title order={5} ta="center" size={"sm"} px="xl">
            Thank you for accepting the request. <br />
            Your property viewing request has been processed successfully
          </Title>
        </Flex>
        {isLoading ? (
          <Center>
            <Loader size={36} />
          </Center>
        ) : (
          // data.map((ele, index) => (
          //   <Flex
          //     key={index}
          //     align={"center"}
          //     justify={"center"}
          //     // bg={"#E3EFD2"}
          //     mx={20}
          //     style={{ border: "1px solid #D9D8D8" }}
          //   >
          //     <div style={{ width: "40%", borderRight: "1px solid #D9D8D8" }}>
          //       <Text>{ele?.title}</Text>
          //     </div>
          //     <div style={{ width: "60%" }}>
          //       <Text lineClamp={1}>{ele?.value}</Text>
          //     </div>
          //   </Flex>

          <Table striped highlightOnHover withTableBorder withColumnBorders>
            <Tbody>
              {data.map((ele, index) => (
                <Tr key={index + ""}>
                  <Td>
                    <Text size="xs" fw={"600"}>
                      {ele?.title}
                    </Text>
                  </Td>
                  <Td>
                    <Text
                      size="sm"
                      c={index === 0 ? "#f30051" : "dimmed"}
                      td={index === 0 ? "underline" : undefined}
                    >
                      {index === 0 ? (
                        <Link
                          href={`/property-detail?property_id=${
                            propertyData?.property?.id
                          }&updateKey=${
                            propertyData?.property?.type ?? "internal"
                          }`}
                        >
                          {ele?.value}
                        </Link>
                      ) : (
                        ele?.value
                      )}
                    </Text>
                  </Td>
                </Tr>
              ))}
            </Tbody>
          </Table>
        )}
        <Center mt={"lg"}>
          <Title order={5} c={"#000"} size={"md"}>
            Visit{" "}
            <Link
              style={{
                color: "#f30051",
                textDecoration: "underline",
              }}
              href={"/"}
            >
              Home
            </Link>{" "}
            page
          </Title>
        </Center>
      </Paper>
    </section>
  );
}

export default InvitationAcceptThankyou;
