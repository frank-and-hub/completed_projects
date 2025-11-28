"use client";
import AdvanceFilter from "@/app/home/components/advanceFilter/AdvanceFilter";
import PropertyServiceHeading from "@/app/home/components/PropertyList/PropertyServiceHeading";
import MaximumLimitAlert from "@/app/property-needs/MaximumLimitAlert";
import CustomButton from "@/components/customButton/CustomButton";
import CustomModal from "@/components/customModal/CustomModal";
import CustomText from "@/components/customText/CustomText";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import {
  Accordion,
  Box,
  Card,
  Flex,
  Grid,
  Group,
  SimpleGrid,
  Title,
} from "@mantine/core";
import { IconBath, IconBed, IconBuilding, IconMapPin } from "@tabler/icons-react";
import dayjs from "dayjs";
import { useState } from "react";
import MatchedProperty from "../matchedProperty/MatchedProperty";
import useRequestCard from "./useRequestCard";

function RequestCard({
  index,
  item,
}: {
  index: number;
  item: requestListItemType;
}) {
  const [value, setValue] = useState<string | null>(null);
  const { userDetail, dispatch, setContextValue } = useRequestCard();
  const {
    city,
    created_at,
    end_price,
    fully_furnished,
    garage,
    garden,
    move_in_date,
    no_of_bathroom,
    no_of_bedroom,
    parking,
    pet_friendly,
    pool,
    property_type,
    start_price,
    country,
    advanced_feature,
    province,
    suburb,
    currency,
  } = item;

  return (
    <Card className="request-card-container" shadow="lg" mb={"md"}>
      <Grid flex={1}>
        <Grid.Col span={{ base: 12, md: 1.5 }}>
          <Box className="request-card-index" p={"xs"}>
            <Box>
              <Title order={1}>{dayjs(created_at).format("DD")}</Title>
              <Title order={5}>{dayjs(created_at).format("MMM YYYY")}</Title>
            </Box>
          </Box>
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 10.5 }}>
          <Box className="request-card-content" px={"md"}>
            <Flex wrap={"wrap"} gap={"sm"}>
              <div className="location_property">
                <Flex>
                  <Title order={3} me={"sm"}>
                    {item?.property_type}
                  </Title>
                </Flex>
                <Group gap={"0.1rem"}>
                  <IconMapPin stroke={1.75} />
                  <CustomText c={"dimmed"}>
                    {capitalizeFirstLetter(item?.country?.country)}
                  </CustomText>
                </Group>
                <Group gap={"0.1rem"}>
                  <IconBed stroke={1.75} size={20} title="No of Bedroom" />
                  <CustomText c={"dimmed"} size="xs">
                    {no_of_bedroom} {" "} Beds
                  </CustomText>{" "}
                
                  <IconBath stroke={1.75} size={15} />
                  <CustomText c={"dimmed"} size="xs">
                    {no_of_bathroom} {" "} Baths
                  </CustomText>
                </Group>
              </div>
              <Flex align={"flex-end"} direction={"column"}>
                <CustomText size="lg" fw={"bold"} c={"#f30051"}>
                  {item?.currency?.currency_symbol} {item?.start_price} -{" "}
                  {item?.currency?.currency_symbol} {item?.end_price}
                </CustomText>
                <CustomText c="dimmed" size="xs">
                  &nbsp; per month
                </CustomText>
              </Flex>
            </Flex>
            {/* <CustomText c="dimmed" size="sm" my={"xs"}>
          {item?.complete_address ?? "N/A"}
        </CustomText> */}
            <Box my={"xl"}>
              <PropertyServiceHeading
                MoveInDate={item?.move_in_date}
                Suburb={item?.suburb?.suburb_name}
                province_name={item?.province?.province_name}
                cityName={item?.city?.city}
              />
            </Box>
            {/* <PropertyService
          isIconDisabled
          Parking={item?.parking}
          fully_furnished={item?.fully_furnished}
          garage={item?.garage}
          garden={item?.garden}
          no_of_bathroom={item?.no_of_bathroom}
          no_of_bedroom={item?.no_of_bedroom}
          pool={item?.pool}
          pet_friendly={item?.pet_friendly}
          advanced_features={item?.advanced_feature}
        /> */}
            {/* <Group>
          <CustomText fw={"500"} size="xs">
            {item?.no_of_bedroom} Bedrooms
          </CustomText>
          <CustomText fw={"500"} size="xs">
            {item?.no_of_bathroom} Bathrooms
          </CustomText>
        </Group> */}
            <Accordion
              chevronPosition="right"
              variant="contained"
              mt={"md"}
              value={value}
              onChange={setValue}
            >
              <Accordion.Item value="item-1">
                <Accordion.Control icon={<IconBuilding />}>
                  <Title order={6}>
                    {item?.property_count} Properties Found
                  </Title>
                </Accordion.Control>
                <Accordion.Panel>
                  {value === "item-1" && <MatchedProperty id={item?.id} />}
                </Accordion.Panel>
              </Accordion.Item>
            </Accordion>
            <Flex align={"center"} justify={"flex-end"} mt={"md"}>
              <CustomModal
                onClose={() => {
                  setContextValue((prev: contextValuesType) => ({
                    ...prev,
                    isSearchApiCall: false,
                    propertySearchData: {},
                    advanceFeatureData: {},
                    advanceFeatureSelectedData: [],
                    requestAgainData: {},
                  }));
                }}
                className={
                  userDetail?.total_request === 5 && !userDetail?.subscription
                    ? "limite_modal_custom"
                    : "comman_modal_custom_next"
                }
                actionButton={
                  <CustomButton
                    onClick={() => {
                      !(
                        userDetail?.total_request === 5 &&
                        !userDetail?.subscription
                      ) &&
                        setContextValue((prev: any) => ({
                          ...prev,
                          isSearchApiCall: true,
                          propertySearchData: {
                            city: city?.city,
                            end_price,
                            start_price,
                            move_in_date,
                            no_of_bathroom,
                            no_of_bedroom,
                            property_type,
                            province_name: province?.province_name,
                            suburb_name: suburb?.suburb_name,
                            country_name: country?.country,
                          },
                          cityId: city?.id,
                          province_Id: province?.id,
                          suburbId: suburb?.id,
                          country_Id: country?.id,
                          requestAgainData: advanced_feature,
                          currency: currency?.currency_symbol,
                        }));
                      if (
                        userDetail?.total_request === 5 &&
                        !userDetail?.subscription
                      ) {
                        dispatch(
                          updatePropertyInformation({
                            city: city?.city,
                            end_price,
                            start_price,
                            no_of_bathroom,
                            no_of_bedroom,
                            property_type,
                            suburb_name: suburb?.suburb_name,
                            province_name: province?.province_name,

                            move_in_date,
                            country_name: country?.country,
                          })
                        );
                        dispatch(updatePropertySearch(true));
                      }
                    }}
                  >
                    Request Again
                  </CustomButton>
                }
              >
                {userDetail?.total_request === 5 &&
                  !userDetail?.subscription ? (
                  <MaximumLimitAlert />
                ) : (
                  <AdvanceFilter isFromSearch={true} />
                )}
              </CustomModal>
            </Flex>
          </Box>
        </Grid.Col>
      </Grid>
    </Card>
  );
}

export default RequestCard;
const matchedProperties = [
  {
    title: "Affordable 2-Bedroom Home for Sale",
    name: "John Doe",
    date: "14-Mar-2025",
    time: "02:30 PM",
    price: "R 6 000",
    likes: 25,
    image: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c",
  },
  {
    title: "Newly Renovated Apartment for Rent",
    name: "Jane Smith",
    date: "14-Mar-2025",
    time: "03:45 PM",
    price: "R 6 000",
    likes: 13,
    image: "https://images.unsplash.com/photo-1568605114967-8130f3a36994",
  },
  {
    title: "Spacious Family Home with Private Garden",
    name: "Bob Jones",
    date: "14-Mar-2025",
    time: "09:15 AM",
    price: "R 6 000",
    likes: 47,
    image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267",
  },
];
