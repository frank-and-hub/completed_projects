"use client";
import { Box, Container, Grid, Loader } from "@mantine/core";
import Image from "next/image";
import "./propertyDetail.scss";
// import HomeIcon from "../../../assets/svg/home_icon.svg";
import CustomModal from "@/components/customModal/CustomModal";
import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import LocationIcon from "../../../assets/svg/locationIconYellow.svg";
import AuthModal from "../auth/AuthModal";
import AgentInformation from "./AgentInformation";
import ProductSlider from "./ProductSlider";
import PropertyFeatures from "./PropertyFeatures";
import usePropertyDetail from "./usePropertyDetail";
// export function generateMetadata() {
//   return {
//     title: "Property Details | PocketProperty ",
//     description:
//       "View complete details, images, features, and amenities for your selected rental property on PocketProperty.",
//     robots: "noindex, nofollow, noarchive",
//   };
// }

const PropertyDetails = () => {
  const {
    data,
    isPending,
    form,
    enquirySubmitHandler,
    enquiryLoading,
    isNewModalOpen,
    setIsNewModalOpen,
    isFromMap,
    mapLoading,
    mapPropertyDetail,
    advanceFeatureData,
    externalPending,
    internalPending,
    dummyLoading,
  } = usePropertyDetail();

  const isMap =
    isFromMap === "dummy" ? true : isFromMap === "internal" ? true : false;
  const isDummy = isFromMap === "dummy" ? true : false;
  return (
    <>
      <section className="product_detail_sec">
        {(
          isMap || isDummy
            ? dummyLoading || mapLoading || internalPending
            : isPending || externalPending
        ) ? (
          <Loader
            style={{
              position: "absolute",
              top: "50%",
              left: "50%",
              transform: "translate(-50%, -50%)",
            }}
          />
        ) : (
          <Container size={"lg"}>
            {(isMap ? mapPropertyDetail?.data : data?.data) ? (
              <>
                <Grid>
                  <Grid.Col
                    className="agent_info_card_container"
                    span={
                      isDummy
                        ? 12
                        : mapPropertyDetail?.data?.property_handle_details ||
                          !isMap
                        ? 8
                        : 12
                    }
                  >
                    <Box className="productslider_box box-container-card">
                      <span className="title_for_product">Property Photos</span>
                      <ProductSlider
                        isDummy={isDummy}
                        images={
                          (isMap
                            ? mapPropertyDetail?.data?.photos!
                            : data?.data?.photos!) as any
                        }
                      />
                    </Box>

                    <div className="box-container-card title_for_product_container">
                      <span className="title_for_product">
                        Property Basic Info
                      </span>
                      <div className="rent_head_card ">
                        <Box className="rent_title_col_1">
                          <h5>
                            {capitalizeFirstLetter(
                              isMap
                                ? mapPropertyDetail?.data?.title ?? ""
                                : data?.data?.title ?? ""
                            )}
                            {/* <span>For Rent</span> */}
                          </h5>
                          {mapPropertyDetail?.data?.address ||
                          data?.data?.address ? (
                            <p>
                              <Image src={LocationIcon} alt="HomeIcon" />{" "}
                              {isMap
                                ? mapPropertyDetail?.data?.address
                                : data?.data?.address}
                            </p>
                          ) : null}
                        </Box>
                        <Box className="rent_title_col_2">
                          <h4 style={{ whiteSpace: "nowrap" }}>
                            {isMap
                              ? mapPropertyDetail?.data?.currency_symbol! +
                                " " +
                                mapPropertyDetail?.data?.price
                              : data?.data?.currency_symbol! +
                                " " +
                                data?.data?.price}{" "}
                            {/* <span>
                              {isMap
                                ? mapPropertyDetail?.data?.currency
                                : data?.data?.currency}
                            </span> */}
                          </h4>
                        </Box>
                      </div>
                    </div>
                  </Grid.Col>
                  <Grid.Col
                    span={4}
                    hidden={isDummy}
                    className="agent_info_card_container"
                  >
                    {mapPropertyDetail?.data?.property_handle_details ||
                    !isMap ? (
                      <AgentInformation
                        agentDetail={
                          mapPropertyDetail?.data?.property_handle_details
                            ? [mapPropertyDetail?.data?.property_handle_details]
                            : data?.data?.contacts!
                        }
                        isAgent={
                          mapPropertyDetail?.data?.property_handle_details
                            ?.role !== "privatelandlord"
                        }
                        enquirySubmitHandler={enquirySubmitHandler}
                        form={form}
                        enquiryLoading={enquiryLoading}
                        setIsNewModalOpen={setIsNewModalOpen}
                        clientLogo={
                          mapPropertyDetail?.data?.client?.logo ??
                          data?.data?.client?.logo!
                        }
                        clientName={
                          mapPropertyDetail?.data?.client?.name ??
                          data?.data?.client?.name!
                        }
                      />
                    ) : null}
                  </Grid.Col>
                </Grid>

                <PropertyFeatures
                  advanceFeatureData={advanceFeatureData}
                  bath={
                    isMap ? mapPropertyDetail?.data?.baths! : data?.data?.baths!
                  }
                  bed={
                    isMap ? mapPropertyDetail?.data?.beds! : data?.data?.beds!
                  }
                  buildSize={
                    isMap
                      ? mapPropertyDetail?.data?.buildingSize!
                      : data?.data?.buildingSize!
                  }
                  buildSizeType={
                    isMap
                      ? mapPropertyDetail?.data?.buildingSize_unit!
                      : data?.data?.buildingSizeType!
                  }
                  description={
                    isMap
                      ? mapPropertyDetail?.data?.description!
                      : data?.data?.description!
                  }
                  furnished={data?.data?.furnished!}
                  garages={Boolean(data?.data?.garages)}
                  garden={false}
                  landSize={
                    isMap
                      ? mapPropertyDetail?.data?.landSize!
                      : data?.data?.landSize!
                  }
                  landSizeType={
                    isMap
                      ? mapPropertyDetail?.data?.landSize_unit!
                      : data?.data?.landsizeType!
                  }
                  parking={Boolean(data?.data?.openparkings)}
                  petsAllowed={data?.data?.petsAllowed!}
                  pool={data?.data?.pool!}
                  otherFeature={data?.data?.other_features!}
                  type={
                    isMap
                      ? mapPropertyDetail?.data?.propertyType!
                      : data?.data?.propertyType!
                  }
                />
              </>
            ) : (
              <span
                style={{
                  position: "absolute",
                  top: "50%",
                  left: "50%",
                  transform: "translate(-50%, -50%)",
                }}
              >
                Property not found
              </span>
            )}
          </Container>
        )}
      </section>
      <CustomModal
        actionButton={null}
        isOpen={isNewModalOpen}
        onClose={() => {
          setIsNewModalOpen("");
        }}
      >
        <AuthModal type="chooseUserType" />
      </CustomModal>
    </>
  );
};

export default PropertyDetails;
