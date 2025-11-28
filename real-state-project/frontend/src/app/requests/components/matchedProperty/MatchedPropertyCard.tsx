import { shareCivilReport } from "@/api/request/request";
import { UploadContract } from "@/app/portals/uploadContract/UploadContract";
import CustomModal from "@/components/customModal/CustomModal";
import CustomText from "@/components/customText/CustomText";
import { useAppSelector } from "@/store/hooks";
import { getBaseURl } from "@/utils/createIconUrl";
import hasDateTimePassed, { showDate } from "@/utils/hasDateTimePassed";
import { closeNotification, notification } from "@/utils/notification";
import useCreditReportGenerate from "@/utils/useCreditReportGenerate";
import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import {
  Box,
  Button,
  Card,
  Checkbox,
  Flex,
  Image,
  Paper,
  Text,
  Title,
  Tooltip,
} from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import { modals } from "@mantine/modals";
import { IconCalendarWeekFilled } from "@tabler/icons-react";
import { useQueryClient } from "@tanstack/react-query";
import dayjs from "dayjs";
import Link from "next/link";
import React, { useMemo } from "react";

function MatchedPropertyCard({
  index,
  property,
  queryKey,
  request_id,
}: {
  index: number;
  property: propertyListItemByID;
  queryKey: (string | null)[];
  request_id: string;
}) {
  const queryClient = useQueryClient();
  const [opened, { open, close }] = useDisclosure(false);
  const userDetail = useAppSelector((state) => state.userReducer?.userDetail);
  const { generateCreditReport, isCreditReportAvailable } = useCreditReportGenerate();
  const dateAndTime = useMemo(() => {
    const ev = property?.property?.event;
    return ev && ev?.date_time ? {
        id: ev?.id,
        date: showDate(ev?.date_time),
        time: dayjs(ev?.date_time).format("hh:mm A"),
        status: ev?.status,
        time_limit: ev?.time_limit,
      } : null;
  }, [property]);

  const handleShare = async () => {
    try {
      notification({
        message: "Sharing credit report with property owner",
        type: "loading",
        autoClose: false,
      });
      await shareCivilReport({
        client_id: property?.property?.property_handle_details?.id,
        property_id: property?.property?.id,
        search_id: request_id,
        status:
          property?.credit_reports_status === "approved"
            ? "unapproved"
            : "approved",
        user_id: userDetail?.id!,
        property_type: property?.details_url,
      });

      await queryClient.invalidateQueries({
        queryKey,
      });
    } catch (error) {
      closeNotification();
      notification({
        message: "Error sharing credit report",
        type: "error",
      });
    } finally {
      closeNotification();
    }
  };

  return (
    <Link
      target="_blank"
      href={`/property-detail?property_id=${property?.property?.id}&updateKey=${property?.details_url ?? "internal"
        }`}
    >
      <Card
        className="request-card-container"
        withBorder
        mb={"md"}
        onClick={() => { }}
        style={{
          flexDirection: "column",
        }}
      >
        <Flex wrap={"wrap"} gap={"sm"}>
          <Image src={property?.image} className="property-image" />
          <Box flex={1}>
            <Flex wrap={"wrap"} gap={"sm"} justify={"space-between"}>
              <Box>
                <Title order={5}>{property?.title}</Title>
                <Title order={6} c={"dimmed"}>
                  {property?.property?.property_handle_details?.fullName}
                </Title>
                <Flex align={"center"} wrap={"wrap"}>
                  <Title c="#f30051" order={5}>
                    {property?.property?.currency_symbol}{" "}
                    {property?.property?.price}&nbsp;
                  </Title>
                  <CustomText c={"dimmed"} size="xs">
                    per month
                  </CustomText>
                </Flex>
              </Box>
              {/* <Flex w={"100%"} align={"center"} justify={"space-between"}> */}
              <Box>
                {dateAndTime && (
                  <Tooltip label="Property viewing request" withArrow>
                    <Paper
                      withBorder
                      bg={
                        hasDateTimePassed(dateAndTime?.date, dateAndTime?.time)
                          ? "var(--mantine-color-gray-4)"
                          : "#f30051"
                      }
                      px={"xs"}
                      py={"xs"}
                      shadow="xs"
                      style={{ alignSelf: "baseline" }}
                    >
                      <Flex align={"center"} wrap={"wrap"}>
                        <Box visibleFrom="sm" me={"xs"}>
                          <IconCalendarWeekFilled
                            color={
                              hasDateTimePassed(
                                dateAndTime?.date,
                                dateAndTime?.time
                              )
                                ? "var(--mantine-color-gray-6)"
                                : "#FFF"
                            }
                            size={18}
                          />
                          {/* &nbsp; */}
                        </Box>
                        <Title
                          order={5}
                          size="xs"
                          c={
                            hasDateTimePassed(
                              dateAndTime?.date,
                              dateAndTime?.time
                            )
                              ? "var(--mantine-color-gray-6)"
                              : "#FFF"
                          }
                          ta={"center"}
                        >
                          {/* {property?.property?.event?.date} -{" "}
                      {property?.property?.event?.time} */}
                          {dateAndTime?.date} - {dateAndTime?.time}
                        </Title>
                      </Flex>
                    </Paper>
                  </Tooltip>
                )}
                {property?.contract ? (
                  <Flex
                    className="contract_link-container"
                    direction={"column"}
                  >
                    {property?.contract ? (
                      <Tooltip label="View contract" withArrow>
                        <Link
                          href={getBaseURl() + "/" + property?.contract}
                          target="_blank"
                          onClick={(event) => {
                            event.stopPropagation();
                          }}
                        >
                          <Button mt={"sm"} w={"100%"}>
                            <div className="contract_link">
                              <span> View Contract</span>
                            </div>
                          </Button>
                        </Link>
                      </Tooltip>
                    ) : null}
                    {property?.contract_status ||
                      property?.contract_status === "rejected" ||
                      property?.contract_status === "tenant_pending" ? (
                      <Tooltip label="Upload contract" withArrow>
                        <Button
                          onClick={(event) => {
                            event.stopPropagation();
                            event.preventDefault();
                            open();
                          }}
                          style={{ marginTop: "10px" }}
                        >
                          <div className="contract_link">
                            <span> Upload Contract</span>
                          </div>
                        </Button>
                      </Tooltip>
                    ) : null}
                  </Flex>
                ) : null}
              </Box>
              {/* </Flex> */}
            </Flex>
          </Box>
        </Flex>
        <Checkbox
          mt={"sm"}
          checked={property?.credit_reports_status === "approved"}
          onClick={async () => {
            !isCreditReportAvailable
              ? modals.openConfirmModal({
                title: "Are you sure?",
                children: (
                  <Text size="xs">
                    Your Credit report has not been generated yet. Please
                    proceed to generate your credit report first.
                  </Text>
                ),

                labels: { confirm: "Continue", cancel: "Cancel" },
                onCancel: () => console.log("Cancel"),
                onConfirm: generateCreditReport,
                centered: true,
              })
              : modals.openConfirmModal({
                title: "Are you sure?",
                children: (
                  <Text size="xs">
                    {property?.credit_reports_status !== "approved"
                      ? "Would you be interested in sharing your credit report with this property owner to establish greater trust?"
                      : "Would you like to unshare your credit report with this property owner?"}
                  </Text>
                ),

                labels: { confirm: "Confirm", cancel: "Cancel" },
                onCancel: () => console.log("Cancel"),
                onConfirm: handleShare,
                centered: true,
              });
          }}
          label="Share your Credit Report?"
        />
      </Card>

      <CustomModal actionButton={null} isOpen={opened} onClose={close}>
        <UploadContract
          contract_id={property?.contract_id}
          admin_id={property?.admin_id}
          queryKey={queryKey}
        />
      </CustomModal>
    </Link>
  );
}

export default MatchedPropertyCard;
