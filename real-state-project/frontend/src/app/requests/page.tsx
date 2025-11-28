"use client";
import {
  Button,
  Center,
  Container,
  Flex,
  Grid,
  Group,
  Loader,
  Pagination,
  Tooltip,
} from "@mantine/core";
import loginImage from "../../../assets/svg/portals_img.svg";
import PropertyHeader from "../propertyNeeds/components/PropertyHeader";
import "./Requests.style.scss";
import RequestCard from "./components/requestCard/RequestCard";
import useRequest from "./useRequest";
import { IconCalendarMonth, IconPlus } from "@tabler/icons-react";
import { DatePickerInput } from "@mantine/dates";
import CustomModal from "@/components/customModal/CustomModal";
import AdvanceFilter from "../home/components/advanceFilter/AdvanceFilter";
import CustomText from "@/components/customText/CustomText";
import { Suspense } from "react";
function Requests() {
  const {
    data,
    isLoading,
    form: {
      getInputProps,
      values: { start_date, end_date },
    },
    totalDays,
    queryKey,
    onPageSet,
    page,
  } = useRequest();
  const icon = (
    <IconCalendarMonth
      style={{ color: "#F30051", width: 18, height: 18 }}
      stroke={1.5}
    />
  );
  return (
    <Suspense>
      <div style={{ background: "#f1f1f1" }}>
        <PropertyHeader
          title="Requests"
          description="Follow your property journey."
          image={loginImage}
        />
        <Container
          className="requests-container"
          size={"xl"}
          style={{
            // paddingTop: 70,
            minHeight: "70vh",
            display: "flex",
            flexDirection: "column",
            flex: 1,
          }}
          my={"lg"}
        >
          <Flex
            my={"md"}
            wrap={"wrap"}
            gap={"md"}
            justify={"center"}
            align={"center"}
          >
            <h6>
              Showing <strong>{data?.total_count} Properties</strong> placed in{" "}
              <strong>{Math.floor(totalDays) + 1} days</strong> from
            </h6>
            {/* <Group ms={"md"}> */}
            <Grid ms={"md"} align="center" justify="center">
              <Grid.Col span={{ base: 12, sm: 5.5 }}>
                <DatePickerInput
                  leftSection={icon}
                  placeholder="Select Date"
                  maxDate={end_date}
                  style={{
                    whiteSpace: "nowrap",
                  }}
                  valueFormat="MMM DD, YYYY"
                  {...getInputProps("start_date")}
                />
              </Grid.Col>
              <Grid.Col span={{ base: 12, sm: 1 }}>
                <Center>
                  <strong>to</strong>
                </Center>
              </Grid.Col>
              <Grid.Col span={{ base: 12, sm: 5.5 }}>
                <DatePickerInput
                  style={{
                    whiteSpace: "nowrap",
                  }}
                  valueFormat="MMM DD, YYYY"
                  leftSection={icon}
                  placeholder="Select Date"
                  minDate={start_date}
                  maxDate={new Date()}
                  {...getInputProps("end_date")}
                  disabled={!!!start_date}
                />
              </Grid.Col>
            </Grid>
          </Flex>
          {/* 
        {isLoading ? (
          <Center flex={1}>
            <Loader size={36} />
          </Center>
        ) : (
          new Array(10)
            .fill(0)
            .map((_, index) => <RequestCard key={index} index={index} />)
        )} */}

          {isLoading ? (
            <Center flex={1}>
              <Loader size={36} />
            </Center>
          ) : data?.data?.length ? (
            <div>
              <Grid>
                <Grid.Col span={12} style={{ paddingTop: 20 }}>
                  {data?.data?.map((item, index) => {
                    return (
                      <RequestCard item={item} key={item?.id} index={index} />
                    );
                  })}
                </Grid.Col>
              </Grid>
              {data?.meta?.total_page === 1 ? null : (
                <Center>
                  <Pagination
                    total={data?.meta?.total_page}
                    withEdges
                    value={Number(data?.meta?.current_page)}
                    onChange={onPageSet}
                  />
                </Center>
              )}
            </div>
          ) : (
            <Center
              h={"30vh"}
              style={{
                justifyContent: "center",
                alignItems: "center",
              }}
            >
              <p
                style={{
                  // position: "absolute",
                  // top: "50%",
                  // left: "50%",
                  // transform: "translate(-50%, 0)",
                  padding: 0,
                }}
              >
                No data found
              </p>
            </Center>
          )}
        </Container>
      </div>
    </Suspense>
  );
}

export default Requests;
