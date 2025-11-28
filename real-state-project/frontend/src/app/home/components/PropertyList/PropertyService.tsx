"use client";
import { Grid, List } from "@mantine/core";
import React, { useMemo } from "react";
import Image from "next/image";
import icon1 from "../../../../../assets/images/icon1.png";
import icon2 from "../../../../../assets/images/icon2.png";
import icon3 from "../../../../../assets/images/icon3.png";
import icon4 from "../../../../../assets/images/icon4.png";
import icon5 from "../../../../../assets/images/icon5.png";
import icon6 from "../../../../../assets/images/icon6.png";
import icon7 from "../../../../../assets/images/icon7.png";
import icon8 from "../../../../../assets/images/icon8.png";
import {
  addSpaceInString,
  capitalizeFirstLetter,
} from "@/utils/capitalizeFiesrtLetter";
import createIconUrl from "@/utils/createIconUrl";

const dataSer = [
  {
    heading: "Parking",
    subHeading: "Yes",
    imageIcon: icon1,
  },
  {
    heading: "Number of bedrooms",
    subHeading: "4",
    imageIcon: icon2,
  },
  {
    heading: "Garden",
    subHeading: "Yes",
    imageIcon: icon3,
  },
  {
    heading: "Pet friendly",
    subHeading: "Yes",
    imageIcon: icon4,
  },
  {
    heading: "Garage",
    subHeading: "Yes",
    imageIcon: icon5,
  },
  {
    heading: "Number of bathrooms",
    subHeading: "4",
    imageIcon: icon6,
  },
  {
    heading: "Furnished",
    subHeading: "Full Furnished",
    imageIcon: icon7,
  },
  {
    heading: "Pool",
    subHeading: "Yes",
    imageIcon: icon8,
  },
];

interface PropertyServiceType {
  Parking: number;
  pool: number;
  fully_furnished: number;
  garage: number;
  garden: number;
  no_of_bedroom: string;
  no_of_bathroom: string;
  pet_friendly: number;
  advanced_features: { [key: string]: { [key: string]: Array<string> } };
  isIconDisabled?: boolean;
}
function PropertyService({
  Parking,
  fully_furnished,
  garage,
  garden,
  no_of_bathroom,
  no_of_bedroom,
  pool,
  pet_friendly,
  advanced_features,
  isIconDisabled,
}: PropertyServiceType) {
  const advanceFeatureData: Array<{ title: string; content: Array<string> }> =
    useMemo(() => {
      if (advanced_features) {
        const arr: Array<{ title: string; content: Array<string> }> = [];
        Object.values(advanced_features)?.map((item) => {
          for (let key in item) {
            arr.push({ title: key, content: item?.[key] });
          }
        });
        return arr;
      }
      return [];
    }, [advanced_features]);
  return (
    <div>
      {advanced_features ? (
        <List className="PropertyService_info_list">
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon2} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Number of bedrooms</h6>
              <h5>{no_of_bedroom}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon6} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Number of bathrooms</h6>
              <h5>{no_of_bathroom}</h5>
            </div>
          </List.Item>
        </List>
      ) : null}
      {advanced_features || advanceFeatureData?.length ? (
        <List className="PropertyService_info_list_new">
          {advanceFeatureData?.map((item, index) => {
            console.log(createIconUrl(item?.title));

            return (
              <List.Item key={index}>
                {isIconDisabled ? null : (
                  <figure>
                    {" "}
                    <Image
                      src={createIconUrl(item?.title)}
                      alt="Parking"
                      width={20}
                      height={20}
                    />{" "}
                  </figure>
                )}
                <div className="PropertyService_text">
                  <h6>
                    {capitalizeFirstLetter(item?.title?.split("_").join(" "))}
                  </h6>
                  <div className="property_text_content">
                    {item?.content?.map((i, index) => {
                      return <h5 key={index}> {addSpaceInString(i)}</h5>;
                    })}
                  </div>
                </div>
              </List.Item>
            );
          })}
        </List>
      ) : (
        <List className="PropertyService_info_list">
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon1} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Parking</h6>
              <h5>{Parking ? "Yes" : "No"}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon2} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Number of bedrooms</h6>
              <h5>{no_of_bedroom}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon3} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Garden</h6>
              <h5>{garden ? "Yes" : "No"}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon4} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Pet friendly</h6>
              <h5>{pet_friendly ? "Yes" : "No"}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon5} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Garage</h6>
              <h5>{garage ? "Yes" : "No"}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon6} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Number of bathrooms</h6>
              <h5>{no_of_bathroom}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon7} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Furnished</h6>
              <h5>{fully_furnished ? "Full Furnished" : "No"}</h5>
            </div>
          </List.Item>
          <List.Item>
            {isIconDisabled ? null : (
              <figure>
                {" "}
                <Image src={icon8} alt="Parking" width={20} height={20} />{" "}
              </figure>
            )}
            <div className="PropertyService_text">
              <h6>Pool</h6>
              <h5>{pool ? "Yes" : "No"}</h5>
            </div>
          </List.Item>
        </List>
      )}
    </div>
  );
}

export default PropertyService;
