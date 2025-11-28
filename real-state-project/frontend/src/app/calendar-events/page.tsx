"use client";
import "@/app/home/components/PropertyList/PropertyService.scss";
import { Center, Container, Grid, Group, Loader } from "@mantine/core";
import loginImage from "../../../assets/svg/calender_event.svg";
import PropertyHeader from "../propertyNeeds/components/PropertyHeader";
import EventCard from "./EventCard";
import useCalendarEvents from "./useCalendarEvents";
type Props = {};

// export function generateMetadata() {
//   return {
//     title: "Property Viewing Schedule | PocketProperty ",
//     description:
//       "Manage your scheduled property viewings, appointments, and important dates seamlessly in your PocketProperty calendar.",
//     robots: "noindex, nofollow, noarchive",
//   };
// }

const page = (props: Props) => {
  const { activeEvents, setActiveEvents, eventListQuery } = useCalendarEvents();
  const { data, isLoading, isPending } = eventListQuery;

  return (
    <div className="portals_sec">
      <PropertyHeader
        title="Property Viewing Schedule"
        description="Keep track of all your property viewings in one place."
        image={loginImage}
      />
      <div className="portals_sec_outer_event">
        <Container className="portals_sec_inner" size={"lg"}>
          <Center>
            <Group
              className="button_container"
              style={{ flexDirection: "row-reverse" }}
            >
              <div
                onClick={() => {
                  setActiveEvents("past");
                }}
                className={
                  activeEvents === "past" ? "active_button" : "event_buttons"
                }
              >
                <span>Past Events</span>
              </div>
              <div
                onClick={() => {
                  setActiveEvents("future");
                }}
                className={
                  activeEvents === "future" ? "active_button" : "event_buttons"
                }
              >
                <span>Upcoming Events</span>
              </div>
            </Group>
          </Center>
          {isLoading || isPending ? (
            <Loader
              style={{
                position: "absolute",
                top: "50%",
                left: "50%",
                transform: "translate(50%, 0)",
              }}
            />
          ) : data?.pages?.map((page) => page?.data).flat()?.length ? (
            <Grid>
              <Grid.Col span={12}>
                {data?.pages
                  ?.map((page) => page?.data)
                  .flat()
                  ?.map((item) => {
                    return <EventCard item={item} />;
                  })}
              </Grid.Col>
            </Grid>
          ) : (
            <p
              style={{
                position: "absolute",
                top: "50%",
                left: "50%",
                transform: "translate(-50%, 0)",
                padding: 0,
              }}
            >
              No data found
            </p>
          )}
        </Container>
      </div>
    </div>
  );
};

export default page;
