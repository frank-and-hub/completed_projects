import { Box, Button, Card, Chip, Divider, Flex, Group, Text, Title, Tooltip } from "@mantine/core";
import Image from "next/image";
import Link from "next/link";
import pinIcon from "../../../assets/svg/locationIconYellow.svg";
import UserImage from "../../../assets/images/agent-user.png";

import "./event.scss";
import CustomText from "@/components/customText/CustomText";
import dayjs from "dayjs";
import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import { monthMap, showDate } from "@/utils/hasDateTimePassed";
import { IconTimezone } from "@tabler/icons-react";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import { modals } from "@mantine/modals";
import useMatchedPropertyCard from "../requests/components/matchedProperty/useMatchedPropertyCard";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

interface portalsTypes {
  item: calendarEventListItemType;
}

type statusOption = {
  [key in string]: {
    label: string;
    color: string;
    value: string;
  };
};

let EventStatus: statusOption = {
  expired: {
    label: "Expired",
    color: "var(--mantine-color-red-filled)",
    value: "expired",
  },
  accepted: {
    label: "Scheduled",
    color: "var(--mantine-color-blue-filled)",
    value: "accepted",
  },
  pending: {
    label: "Not Accepted Yet",
    color: "var(--mantine-color-yellow-filled)",
    value: "pending",
  },
  completed: {
    label: "Competed",
    color: "var(--mantine-color-teal-filled)",
    value: "completed",
  },
  cancelled: {
    label: "Cancelled",
    color: "var(--mantine-color-red-filled)",
    value: "cancelled",
  },
  "re-schedule": {
    label: "Rescheduled",
    color: "var(--mantine-color-blue-filled)",
    value: "re-schedule",
  },
};

function EventCard({ item }: portalsTypes) {
  const router = useRouter();
  // const pathname = usePathname();
  // const searchParams = useSearchParams();
  const [localItem, setLocalItem] = useState(item);

  const {
    id,
    title,
    admin: { image, name },
    description,
    time,
    date,
    status,
    property: { address, id: propertyId },
  } = localItem;

  useEffect(() => {
    setLocalItem(item);
  }, [item]);

  const { acceptInviteMutate, declineInviteMutate } = useMatchedPropertyCard();
  dayjs.locale('en');

  const time_limit = dayjs().diff(dayjs(date), 'second');
  return (
    <div className="card_house_event" key={id}>
      <figure>
        <div>
          <h1> {showDate(date).split(" ")?.[0]} </h1>
          <h2>
            {monthMap?.[showDate(date).split(" ")?.[1]]}{" "}
            {showDate(date).split(" ")?.[2]}
          </h2>
          <CustomText size="lg" fw={"bold"} c={"white"}>
            {dayjs(date).format("hh:mm A")}
          </CustomText>
        </div>
      </figure>
      <figcaption className="event_fig_caption" >
        <Flex wrap={"nowrap"}>
          <h3 style={{ flex: 1 }}>{capitalizeFirstLetter(title)}</h3>
          {EventStatus?.[status]?.label ? (
            <Chip
              checked={false}
              style={{ width: "auto" }}
              styles={{
                label: {
                  background: EventStatus?.[status]?.color,
                  color: "#FFF",
                },
              }}
              value={"filled"}
            >
              <Title fw={"500"} size="sm" order={5} c={"#FFF"} mb={0}>
                {EventStatus?.[status]?.label}
              </Title>
            </Chip>
          ) : null}
        </Flex>
        <CustomText
          color="dimmed"
          size="sm"
          my="xs"
          style={{ flex: 1 }}
          lineClamp={3}
        >
          {description}
        </CustomText>
        <Divider size={"1.5px"} mb={"7px"} mt={"7px"} />
        <div style={{ marginTop: 10 }}>
          <div style={{ flex: 1 }}>
            {item?.property?.complete_address && (
              <h5 className="location-text">
                <Image
                  src={pinIcon}
                  alt="no-image"
                  width={20}
                  height={20}
                  className="location-pin-image"
                />
                {item?.property?.complete_address}
              </h5>
            )}
          </div>
          <h6>
            <Image
              src={image ? image : UserImage}
              alt="no-image"
              width={25}
              height={25}
            />
            {capitalizeFirstLetter(name)}
          </h6>
        </div>
        {time_limit < 0 && status === 'pending' ? (
          <>
            <Divider size={"1.5px"} mb={"7px"} mt={"7px"} />
            <Flex  columnGap={`xl`} rowGap={`sm`}  direction={'row'} wrap={{ base: `wrap`, md: 'unset', sm: `unset`, lg: `unset`, xl: `unset` }}>
              <Tooltip label="Accept event" withArrow>
                <Button
                  onClick={(event) => {
                    event.stopPropagation();
                    event.preventDefault();
                    modals.openConfirmModal({
                      title: "Accept Property",
                      children: (
                        <Text size="xs">
                          You want to accept this property?
                        </Text>
                      ),
                      labels: { confirm: "Accept", cancel: "Cancel" },
                      onCancel: () => console.log("Cancel"),
                      onConfirm: () =>
                        acceptInviteMutate(id, {
                          onSuccess: () => {
                            setLocalItem(prev => ({
                              ...prev,
                              status: 'accepted',
                            }));
                          },
                        }),
                      centered: true,
                    });
                  }}
                  className="propertiesCardBtn"
                  w={"100%"}
                  bg={`green`}
                >
                  <div className="contract_link">
                    <span>Accept</span>
                  </div>
                </Button>
              </Tooltip>
              <Tooltip label="Decline event" withArrow>
                <Button
                  onClick={(event) => {
                    event.stopPropagation();
                    event.preventDefault();
                    modals.openConfirmModal({
                      title: "Decline Property",
                      children: (
                        <Text size="xs">
                          You want to decline this property ?
                        </Text>
                      ),
                      labels: { confirm: "Decline", cancel: "Cancel" },
                      onCancel: () => console.log("Cancel"),
                      onConfirm: () => declineInviteMutate(id, {
                        onSuccess: () => {
                          setLocalItem(prev => ({
                            ...prev,
                            status: 'cancelled',
                          }));
                        },
                      }),
                      centered: true,
                    });
                  }}
                  className="propertiesCardBtn"
                  w={"100%"}
                >
                  <div className="contract_link">
                    <span>Decline</span>
                  </div>
                </Button>
              </Tooltip>
              <Tooltip label="Reschedule event" withArrow>
                <Button
                  onClick={(event) => {
                    event.stopPropagation();
                    event.preventDefault();
                    router.push('/reschedule?id=' + id + '&property=' + propertyId)
                  }}
                  className="propertiesCardBtn"
                  w={"100%"}
                  bg={`yellow`}
                >
                  <div className="contract_link">
                    <span>Reschedule</span>
                  </div>
                </Button>
              </Tooltip>
            </Flex>
          </>
        ) : (
          <></>
        )}
        {/* <h6>
            <span>From:</span> {linkHer}{' '}
          </h6> */}
      </figcaption>
    </div>
  );
}

export default EventCard;