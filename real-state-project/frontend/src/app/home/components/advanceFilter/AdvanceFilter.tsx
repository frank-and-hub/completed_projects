"use client";
import {
  getAllCountries,
  getCityList,
  getProvinceList,
  getSuburbList,
} from "@/api/propertySearchHistory/propertySearch";
import CustomApiSelect from "@/components/customApiSelect/CustomApiSelect";
import CustomButton from "@/components/customButton/CustomButton";
import CustomModal from "@/components/customModal/CustomModal";
import CustomText from "@/components/customText/CustomText";
import { useAppDispatch } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import {
  addSpaceInString,
  capitalizeFirstLetter,
} from "@/utils/capitalizeFiesrtLetter";
import {
  ActionIcon,
  Anchor,
  Box,
  Checkbox,
  Collapse,
  Grid,
  Group,
  Loader,
  Select,
  TextInput,
} from "@mantine/core";
import { DatePicker } from "@mantine/dates";
import "@mantine/dates/styles.css";
import { IconChevronDown, IconQuestionMark, IconX } from "@tabler/icons-react";
import dayjs from "dayjs";
import Image from "next/image";
import { useState } from "react";
import "react-datepicker/dist/react-datepicker.css";
import Reload from "../../../../../assets/svg/reload.svg";
import ModalCloseIcon from "../modalCloseIcon/ModalCloseIcon";
import "./AdvanceFilter.scss";
import useAdvanceFilter from "./useAdvanceFilter";
function AdvanceFilter({
  handleClose,
  isFromSearch = false,
}: {
  handleClose?: () => void;
  isFromSearch?: boolean;
}) {
  const {
    resetFilter,
    form: { getInputProps, values, setFieldValue, errors, setValues },
    handleSubmit,
    isPending,
    userDetail,
    setContextValue,
    advanceSearchData,
    onSelectChangeData,
    onSelectHandler,
    propertyLoading,
    selectedData,
    onRemoveHandler,
    currencyData,
    searchValue,
    id,
    onMouseEnter,
    onCloseDropDown,
    opened,
    idData,
    setIdData,
    defaultCountryData,
    defaultCityData,
    defaultProvinceData,
    defaultSuburbData,
    checkItem,
  } = useAdvanceFilter({ handleClose, isFromSearch });
  const { move_in_date, no_of_bathroom, no_of_bedroom } = values;
  const dispatch = useAppDispatch();
  const [isModalOpen, setIsModalOpen] = useState("");
  const OpenModal = ({ handleClose }: any) => {
    return (
      <>
        <div style={{ position: "absolute", top: 10, right: 10 }}>
          <ModalCloseIcon handleClose={() => handleClose()} />
        </div>
        <DatePicker
          onChange={(date) => {
            setFieldValue("move_in_date", date);
            handleClose();
          }}
          minDate={new Date()}
        />
      </>
    );
  };
  return (
    <Box className="advance_filter_modal">
      <div className="modal_head_close">
        <h2>Advance Filters</h2>
        <ModalCloseIcon handleClose={handleClose} isFromAdvanceFilter />
      </div>

      {propertyLoading ? (
        <Loader
          style={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
          }}
          color="blue"
        />
      ) : (
        <>
          <Grid className="advance_filter_info">
            <Grid.Col span={6}>
              <CustomApiSelect
                externalValue={defaultCountryData}
                labelKey="name"
                label="Country"
                placeholder="Select Country Name"
                queryFn={getAllCountries}
                {...getInputProps("country_name")}
                onChange={(value, additionalData) => {
                  setIdData((prev) => ({
                    ...prev,
                    countryId: String(additionalData?.id),
                    currency: additionalData?.item?.currency_symbol,
                  }));

                  getInputProps("country_name").onChange(additionalData?.label);
                  if (additionalData?.item?.currency_symbol) {
                    setValues((prev) => ({
                      ...prev,
                      city: "",
                      province_name: "",
                      suburb_name: "",
                    }));
                    setIdData((prev) => ({
                      ...prev,
                      cityId: "",
                      provinceId: "",
                      suburbId: "",
                    }));
                    setContextValue((prev: contextValuesType) => ({
                      ...prev,
                      propertySearchData: {},
                      country_Id: 0,
                      suburbId: "",
                      cityId: "",
                      province_Id: "",
                      currency: "",
                    }));
                  }
                }}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <CustomApiSelect
                externalValue={defaultProvinceData}
                key={idData?.countryId + ""}
                apiEnabled={!!idData?.countryId}
                labelKey="name"
                label="Province Name"
                placeholder="Select Province Name"
                queryFn={(props) =>
                  getProvinceList({
                    ...props,
                    countryId: String(idData?.countryId),
                  })
                }
                {...getInputProps("province_name")}
                onChange={(value, additionalData) => {
                  setIdData((prev) => ({
                    ...prev,
                    provinceId: String(additionalData?.id),
                  }));
                  if (additionalData?.item) {
                    setValues((prev) => ({
                      ...prev,
                      city: "",
                      suburb_name: "",
                    }));
                    setIdData((prev) => ({
                      ...prev,
                      cityId: "",
                      suburbId: "",
                    }));
                    setContextValue((prev: contextValuesType) => ({
                      ...prev,
                      propertySearchData: {},
                      // country_Id: 0,
                      suburbId: "",
                      cityId: "",
                      province_Id: "",
                    }));
                  }
                  getInputProps("province_name").onChange(
                    additionalData?.label
                  );
                }}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <CustomApiSelect
                externalValue={defaultCityData}
                key={idData?.provinceId + ""}
                apiEnabled={!!idData?.provinceId}
                labelKey="name"
                label="City Name"
                placeholder="Select City Name"
                queryFn={(props) =>
                  getCityList({
                    ...props,
                    provinceId: String(idData?.provinceId),
                  })
                }
                {...getInputProps("city")}
                onChange={(value, additionalData) => {
                  if (additionalData?.item) {
                    setValues((prev) => ({
                      ...prev,
                      suburb_name: "",
                    }));
                    setIdData((prev) => ({
                      ...prev,
                      suburbId: "",
                    }));
                    setContextValue((prev: contextValuesType) => ({
                      ...prev,
                      propertySearchData: {},
                      suburbId: "",
                      cityId: "",
                    }));
                  }
                  setIdData((prev) => ({
                    ...prev,
                    cityId: String(additionalData?.id),
                  }));
                  getInputProps("city").onChange(additionalData?.label);
                }}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <CustomApiSelect
                externalValue={defaultSuburbData}
                key={idData?.cityId + ""}
                apiEnabled={!!idData?.cityId}
                labelKey="name"
                label="Suburb Name"
                placeholder="Select Suburb Name"
                queryFn={(props) =>
                  getSuburbList({
                    ...props,
                    cityId: String(idData?.cityId),
                  })
                }
                {...getInputProps("suburb_name")}
                onChange={(value, additionalData) => {
                  if (additionalData?.item) {
                    setContextValue((prev: contextValuesType) => ({
                      ...prev,
                      propertySearchData: {},
                      suburbId: "",
                    }));
                  }
                  getInputProps("suburb_name").onChange(additionalData?.label);
                  setIdData((prev) => ({
                    ...prev,
                    suburbId: String(additionalData?.id),
                  }));
                }}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <Select
                searchable
                label="Property type"
                placeholder="Please Select Property Type"
                data={[
                  { label: "House", value: "House" },
                  { label: "Townhouse", value: "Townhouse" },
                  { label: "Apartment/Flat", value: "Apartment" },
                  { label: "Studio", value: "Studio" },
                  { label: "Room", value: "Room" },
                  { label: "Cluster", value: "Cluster" },
                  {
                    label: "Cottage / Garden Cottage",
                    value: "Garden Cottage",
                  },
                  { label: "Penthouse", value: "Penthouse" },
                ]}
                {...getInputProps("property_type")}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <Select
                searchable
                label="Currency type"
                placeholder="Please Select Property Type"
                data={currencyData}
                {...getInputProps("currency")}
                disabled
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <TextInput
                type="number"
                label="Minimum price"
                placeholder="Please enter minimum price"
                {...getInputProps("start_price")}
              />
            </Grid.Col>
            <Grid.Col span={6}>
              <TextInput
                type="number"
                label="Maximum price"
                placeholder="Please enter maximum price"
                {...getInputProps("end_price")}
              />
            </Grid.Col>
          </Grid>

          <Grid className="no_of_selects_here">
            <Grid.Col span={6}>
              <div className="no_of_selects">
                <h6>No. of Bedrooms</h6>
                <ul>
                  <li
                    className={no_of_bedroom === "1" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bedroom", "1");
                    }}
                  >
                    <a href="#">1</a>
                  </li>
                  <li
                    className={no_of_bedroom === "2" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bedroom", "2");
                    }}
                  >
                    <a href="#">2</a>
                  </li>
                  <li
                    className={no_of_bedroom === "3" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bedroom", "3");
                    }}
                  >
                    <a href="#">3</a>
                  </li>
                  <li
                    className={no_of_bedroom === "4" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bedroom", "4");
                    }}
                  >
                    <a href="#">4</a>
                  </li>
                  <li
                    className={no_of_bedroom === "5" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bedroom", "5");
                    }}
                  >
                    <a href="#">5+</a>
                  </li>
                </ul>
                {errors?.no_of_bedroom ? (
                  <span className="phone_error">{errors?.no_of_bedroom}</span>
                ) : null}
              </div>
            </Grid.Col>
            <Grid.Col span={6}>
              <div className="no_of_selects">
                <h6>No. of bathrooms</h6>
                <ul>
                  <li
                    className={no_of_bathroom === "1" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bathroom", "1");
                    }}
                  >
                    <a href="#">1</a>
                  </li>
                  <li
                    className={no_of_bathroom === "2" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bathroom", "2");
                    }}
                  >
                    <a href="#">2</a>
                  </li>
                  <li
                    className={no_of_bathroom === "3" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bathroom", "3");
                    }}
                  >
                    <a href="#">3</a>
                  </li>
                  <li
                    className={no_of_bathroom === "4" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bathroom", "4");
                    }}
                  >
                    <a href="#">4</a>
                  </li>
                  <li
                    className={no_of_bathroom === "5" ? "active" : ""}
                    onClick={() => {
                      setFieldValue("no_of_bathroom", "5");
                    }}
                  >
                    <a href="#">5+</a>
                  </li>
                </ul>
                {errors?.no_of_bathroom ? (
                  <span className="phone_error">{errors?.no_of_bathroom}</span>
                ) : null}
              </div>
            </Grid.Col>
          </Grid>
          <h5 className="move_in_date">
            {`Move In Date: ${
              move_in_date ? dayjs(move_in_date).format("DD/MM/YYYY") : ""
            }`}
          </h5>
          <Image
            onClick={() => {
              setIsModalOpen("true");
            }}
            src={require("../../../../../assets/images/Calendar.svg")}
            alt="no-image"
            style={{ marginBottom: 15, cursor: "pointer" }}
          />
          <CustomModal
            className="date_madal"
            actionButton={null}
            isOpen={isModalOpen}
            onClose={() => {
              setIsModalOpen("");
            }}
          >
            <OpenModal />
          </CustomModal>
          {advanceSearchData?.map((item, index) => {
            const useIndex = index;
            return (
              <div className="additional_feature_container" key={useIndex}>
                <div className="hover_item_details"></div>
                <h5>
                  {capitalizeFirstLetter(item?.title.split("_").join(" "))}
                </h5>
                <div className="search_container">
                  <div className="textInput_container">
                    {selectedData?.[String(index)]?.map((item, index) => {
                      const i = index;

                      return (
                        <div className="filter_chip" key={i}>
                          <CustomText color="#fff" mr={"md"} size="sm">
                            {capitalizeFirstLetter(
                              addSpaceInString(item?.value)
                            )}
                          </CustomText>
                          <IconX
                            onClick={() => {
                              onRemoveHandler({
                                index: useIndex,
                                value: item?.value,
                              });
                            }}
                            color="white"
                            size={15}
                            style={{ cursor: "pointer" }}
                          />
                        </div>
                      );
                    })}

                    <TextInput
                      value={searchValue?.[String(index)]}
                      size="sm"
                      placeholder="Search here"
                      onChange={(event) => {
                        onSelectChangeData(event?.currentTarget?.value, index);
                      }}
                    />
                  </div>
                  <Group
                    // onMouseLeave={() => {
                    //   setOpened(false);
                    //   setId(null);
                    // }}
                    // onMouseEnter={() => {
                    //   onMouseEnter(index);
                    // }}
                    onClick={() => {
                      onMouseEnter(index);
                    }}
                    align="center"
                    justify="center"
                    style={
                      {
                        // backgroundColor: "#f30051",
                        // borderRadius: 20,
                        // height: "25px",
                        // width: "25px",
                      }
                    }
                  >
                    <ActionIcon c={"#f30051"} radius={"xl"} ms={"xs"}>
                      <IconChevronDown
                        className={`icon_question_mark ${
                          id === index ? "active-icon_question_mark" : ""
                        }`}
                        stroke={2.5}
                        size={20}
                        color="white"
                        // style={{ backgroundColor: "#f30051", borderRadius: 20 }}
                      />
                    </ActionIcon>
                  </Group>
                </div>
                <Collapse
                  in={opened && id === index}
                  transitionDuration={0}
                  transitionTimingFunction="linear"
                >
                  <Box className="select_additional_features">
                    <IconX
                      onClick={() => {
                        onCloseDropDown(index);
                      }}
                      size={24}
                      style={{
                        position: "absolute",
                        right: 10,
                        top: 10,
                        backgroundColor: "#EDEDED",
                        borderRadius: 20,
                        padding: 3,
                        cursor: "pointer",
                      }}
                      color="#F30051"
                    />
                    {item?.content?.map((i, index) => {
                      const indx = index;
                      return (
                        <div key={indx}>
                          <h4>
                            {capitalizeFirstLetter(
                              i?.title.split("_").join(" ")
                            )}
                          </h4>
                          <ul>
                            {i?.content?.map((it, index) => {
                              const tm = index;

                              return (
                                <li
                                  key={tm}
                                  onClick={() => {
                                    checkItem?.includes(it?.[0])
                                      ? null
                                      : onSelectHandler({
                                          index: useIndex,
                                          name: it?.[0],
                                          title: i?.title,
                                          heading: item?.title,
                                        });
                                  }}
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <Checkbox
                                    mr={10}
                                    iconColor={"#fff"}
                                    color={"#e9e9e9"}
                                    className="checkBox"
                                    checked={checkItem?.includes(it?.[0])}
                                    onChange={(event) =>
                                      checkItem?.includes(it?.[0])
                                        ? onRemoveHandler({
                                            index: useIndex,
                                            value: it?.[0],
                                          })
                                        : onSelectHandler({
                                            index: useIndex,
                                            name: it?.[0],
                                            title: i?.title,
                                            heading: item?.title,
                                          })
                                    }
                                  />
                                  <CustomText>
                                    {capitalizeFirstLetter(
                                      addSpaceInString(it?.[0])
                                    )}
                                  </CustomText>
                                </li>
                              );
                            })}
                          </ul>
                        </div>
                      );
                    })}
                  </Box>
                </Collapse>
              </div>
            );
          })}

          <Box className="reset_all_fliters">
            <Anchor
              underline="always"
              onClick={() => {
                resetFilter();
              }}
            >
              <Image src={Reload} alt="reload" />
              Reset all filters
            </Anchor>

            <CustomButton
              loading={isPending}
              onClick={() => {
                handleSubmit();
              }}
            >
              Next
            </CustomButton>
          </Box>
        </>
      )}
    </Box>
  );
}

export default AdvanceFilter;
