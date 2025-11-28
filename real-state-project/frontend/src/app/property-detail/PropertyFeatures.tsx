import { Box, Card, Grid, List, SimpleGrid, Text } from "@mantine/core";
import Image from "next/image";
import { useSearchParams } from "next/navigation";
import bathroomsIcon from "../../../assets/svg/bathrooms_icon.svg";
import bedroomsIcon from "../../../assets/svg/bedrooms_icon.svg";
import checkIcon from "../../../assets/svg/check_icon.svg";
import furnishedIcon from "../../../assets/svg/furnished_icon.svg";
import garageIcon from "../../../assets/svg/garage_icon.svg";
import parkingIcon from "../../../assets/svg/parking_icon.svg";
import petIcon from "../../../assets/svg/pet_icon.svg";
import poolIcon from "../../../assets/svg/pool_icon.svg";
import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import createIconUrl from "@/utils/createIconUrl";
import { Suspense } from "react";

const OthrFacilit: string[] = [
  "Living Area",
  "Staff Accommodation",
  "Living Area",
  "Car Ports",
  "Living Area",
  "Living Area",
  "Living Area",
];
interface PropertyFeatureType {
  bed: string;
  bath: string;
  buildSize: string;
  buildSizeType: string;
  landSize: string;
  landSizeType: string;
  pool: boolean;
  petsAllowed: boolean;
  garages: boolean;
  furnished: boolean;
  description: string;
  parking: boolean;
  garden: boolean;
  otherFeature: otherFeatureType;
  advanceFeatureData: advanceFeatureDataShowType;
  type: string;
}
function PropertyFeatures({
  bath,
  bed,
  buildSize,
  buildSizeType,
  description,
  furnished,
  garages,
  garden,
  landSize,
  landSizeType,
  parking,
  petsAllowed,
  pool,
  otherFeature,
  advanceFeatureData,
  type,
}: PropertyFeatureType) {
  const searchParams = useSearchParams();
  const isFromMap = searchParams?.get("updateKey");

  const isMap = isFromMap === "internal" ? true : false;
  return (
    <Suspense>
      <div className="property_outer_main ">
        <Card className="card_first_property box-container-card">
          <Text className="title_for_product">Property Details</Text>
          <List
            className="property_size"
            style={{ alignItems: "baseline" }}
            pb={isMap ? "sm" : "2.5rem"}
          >
            <List.Item>
              <div className="d-flex">
                <strong>Type &nbsp;</strong> <br />
                {type ? <span>{type}</span> : <span>-</span>}
              </div>
            </List.Item>
            <List.Item>
              <div className="d-flex">
                <strong>Land Size &nbsp;</strong> <br />
                {landSize ? (
                  <>
                    <span>{landSize}</span>
                    <span>{landSizeType}</span>
                  </>
                ) : (
                  <span>-</span>
                )}
              </div>
            </List.Item>
            <List.Item>
              <div>
                <strong>Building Size &nbsp;</strong> <br />
                {buildSize ? (
                  <>
                    <span>{buildSize}</span>
                    <span>{buildSizeType}</span>
                  </>
                ) : (
                  <span>-</span>
                )}
              </div>
            </List.Item>
            {isMap && (
              <List.Item>
                {/* <figure style={{ marginRight: 10 }}>
                <Image src={bedroomsIcon} alt={bedroomsIcon} />
              </figure> */}
                <div>
                  <strong>Number of bedrooms&nbsp;</strong> <br />
                  {bed ? <span>{bed}</span> : <span>-</span>}
                </div>
              </List.Item>
            )}
            {isMap && (
              <List.Item>
                {/* <figure style={{ marginRight: 10 }}>
                <Image src={bathroomsIcon} alt={bathroomsIcon} />
              </figure> */}
                <div>
                  <strong>Number of bathrooms &nbsp;</strong> <br />
                  {bath ? <span>{bath}</span> : <span>-</span>}
                </div>
              </List.Item>
            )}
          </List>

          {!isMap ? (
            <List className="facilities_info_list">
              <List.Item>
                <figure>
                  <Image src={bedroomsIcon} alt={bedroomsIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Number of bedrooms</h6>
                  {bed ? <h5>{bed}</h5> : <h5>-</h5>}
                </div>
              </List.Item>
              <List.Item>
                <figure>
                  <Image src={bathroomsIcon} alt={bathroomsIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Number of bathrooms</h6>
                  {bath ? <h5>{bath}</h5> : <h5>-</h5>}
                </div>
              </List.Item>
              <List.Item>
                <figure>
                  <Image src={furnishedIcon} alt={furnishedIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Furnished</h6>
                  <h5>{furnished ? "Full Furnished" : "No"}</h5>
                </div>
              </List.Item>
              <List.Item>
                <figure>
                  <Image src={parkingIcon} alt={parkingIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Parking</h6>
                  <h5>{parking ? "Yes" : "No"}</h5>
                </div>
              </List.Item>

              <List.Item>
                <figure>
                  <Image src={poolIcon} alt={poolIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Pool</h6>
                  <h5>{pool ? "Yes" : " No"}</h5>
                </div>
              </List.Item>
              <List.Item>
                <figure>
                  <Image src={garageIcon} alt={garageIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Garage</h6>
                  <h5>{garages ? "Yes" : "No"}</h5>
                </div>
              </List.Item>
              <List.Item>
                <figure>
                  <Image src={petIcon} alt={petIcon} />
                </figure>
                <div className="PropertyService_text">
                  <h6>Pet friendly</h6>
                  <h5>{petsAllowed ? "Yes" : "No"}</h5>
                </div>
              </List.Item>
            </List>
          ) : null}
          {
            !isMap ? (
              <>
                <Box className="facilities_otr_card">
                  <Text className="title_for_product">Other Facilities :</Text>
                  <List className="facilities_otr_list">
                    {otherFeature?.propertyFeatures ? (
                      <List.Item>
                        <Text>
                          <Image src={checkIcon} alt={checkIcon} />{" "}
                          {otherFeature?.propertyFeatures}
                        </Text>
                      </List.Item>
                    ) : null}
                    {otherFeature?.carports ? (
                      <List.Item>
                        <Text>
                          <Image src={checkIcon} alt={checkIcon} /> Car Ports
                        </Text>
                      </List.Item>
                    ) : null}
                    {otherFeature?.staffAccommodation ? (
                      <List.Item>
                        <Text>
                          <Image src={checkIcon} alt={checkIcon} /> Staff
                          Accommodation
                        </Text>
                      </List.Item>
                    ) : null}
                    {otherFeature?.study ? (
                      <List.Item>
                        <Text>
                          <Image src={checkIcon} alt={checkIcon} /> Study
                        </Text>
                      </List.Item>
                    ) : null}
                    {otherFeature?.livingAreas ? (
                      <List.Item>
                        <Text>
                          <Image src={checkIcon} alt={checkIcon} /> Living Area
                        </Text>
                      </List.Item>
                    ) : null}
                  </List>
                </Box>
              </>
            ) : null
            // advanceFeatureData?.map((item, index) => {
            //   const i = index;
            //   return (
            //     <Box key={i} mb={"12px"}>
            //       <div className="title_icon_for_other_facilities">
            //         {/* <figure>
            //           <Image
            //             src={createIconUrl(item?.title)}
            //             alt="Parking"
            //             width={30}
            //             height={30}
            //           />
            //         </figure> */}
            //         <Text className="title_for_other_facilities">
            //           {capitalizeFirstLetter(item?.title.split("_").join(" "))} :
            //         </Text>
            //       </div>
            //       <Grid gutter="xs">
            //         {item?.content?.map((item, index) => {
            //           return (
            //             <Grid.Col span={4} key={index}>
            //               <div>
            //                 <div className="other_facilities_item_container">
            //                   <figure>
            //                     <Image
            //                       src={createIconUrl(item?.title)}
            //                       alt="Parking"
            //                       width={30}
            //                       height={30}
            //                     />
            //                   </figure>
            //                   <strong>
            //                     {capitalizeFirstLetter(
            //                       item?.title.split("_").join(" ")
            //                     )}{" "}
            //                     :
            //                   </strong>
            //                 </div>

            //                 <List className="list-style-feature" mb={"md"}>
            //                   {item?.content?.map((row, index) => {
            //                     const t = row.replace(/([a-z])([A-Z])/g, "$1 $2");
            //                     return (
            //                       <List.Item key={index}>
            //                         <span>
            //                           {capitalizeFirstLetter(t)}
            //                           {/* {item?.content?.length - 1 !== index
            //                             ? ", "
            //                             : ""} */}
            //                         </span>
            //                       </List.Item>
            //                     );
            //                   })}
            //                 </List>
            //               </div>
            //             </Grid.Col>
            //           );
            //         })}
            //       </Grid>
            //     </Box>
            //   );
            // })
          }
        </Card>

        {description && (
          <Card className="card_first_description  box-container-card">
            <Text className="title_for_product">Description</Text>
            <Text className="describ_product">{description}</Text>
          </Card>
        )}
        {advanceFeatureData?.length > 0 && isMap && (
          <Card className="card_first_description  box-container-card">
            {advanceFeatureData?.map((item, index) => {
              const i = index;
              return (
                <Box key={i} mb={"12px"}>
                  <div className="title_icon_for_other_facilities">
                    {/* <figure>
                    <Image
                      src={createIconUrl(item?.title)}
                      alt="Parking"
                      width={30}
                      height={30}
                    />
                  </figure> */}
                    <Text className="title_for_other_facilities">
                      {capitalizeFirstLetter(item?.title.split("_").join(" "))}{" "}
                      :
                    </Text>
                  </div>
                  <Grid gutter="xs">
                    {item?.content?.map((item, index) => {
                      return (
                        <Grid.Col span={4} key={index}>
                          <div>
                            <div className="other_facilities_item_container">
                              <figure>
                                <Image
                                  src={createIconUrl(item?.title)}
                                  alt="Parking"
                                  width={30}
                                  height={30}
                                />
                              </figure>
                              <strong>
                                {capitalizeFirstLetter(
                                  item?.title.split("_").join(" ")
                                )}{" "}
                                :
                              </strong>
                            </div>

                            <List className="list-style-feature" mb={"md"}>
                              {item?.content?.map((row, index) => {
                                const t = row.replace(
                                  /([a-z])([A-Z])/g,
                                  "$1 $2"
                                );
                                return (
                                  <List.Item key={index}>
                                    <span>
                                      {capitalizeFirstLetter(t)}
                                      {/* {item?.content?.length - 1 !== index
                                      ? ", "
                                      : ""} */}
                                    </span>
                                  </List.Item>
                                );
                              })}
                            </List>
                          </div>
                        </Grid.Col>
                      );
                    })}
                  </Grid>
                </Box>
              );
            })}
          </Card>
        )}
      </div>
    </Suspense>
  );
}

export default PropertyFeatures;
