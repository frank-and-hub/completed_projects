"use client";
import CustomText from "@/components/customText/CustomText";
import __DEV__ from "@/utils/devCheck";
import { Box, Center, Container, Flex, Loader } from "@mantine/core";
import { GoogleMap, Marker, MarkerClusterer } from "@react-google-maps/api";
import { useMemo, useState } from "react";
import "./section6.scss";
import useSectionSix from "./useSectionSix";
import { useAppSelector } from "@/store/hooks";
import CustomMarker from "./CustomMarker";
import Script from "next/script";

declare global {
  interface Window {
    google: typeof google;
  }
}

// Default location for South Africa
const center = {
  lat: -30.5595,
  lng: 22.9375,
};

const options = {
  minZoom: 3, // Set the minimum zoom level here
  fullscreenControl: true,
  zoom: 2,
};

const SectionSix = () => {
  const {
    isShowMap,
    debouncedHandleBoundsChanged,
    id,
    onLoad,
    selected,
    setId,
    setSelected,
    isLoaded,
    markers,
    isLoading,
  } = useSectionSix();

  const targetUserLocation = useAppSelector(
    (state: any) => state.userReducer.userDetail?.location
  );

  const userLocation = useMemo(
    () => ({
      lat: targetUserLocation?.lat
        ? Number(targetUserLocation.lat)
        : center?.lat,
      lng: targetUserLocation?.lng
        ? Number(targetUserLocation.lng)
        : center?.lng,
    }),
    [targetUserLocation]
  );

  return isShowMap ? (
    <div className="map_container">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>Find On Map</p>
          </Box>
          <CustomText mb={"20px"} mt={"sm"} ml={"md"} fw={"600"} size="30px">
            Explore properties
          </CustomText>
          <CustomText size="14px">
            This map shows properties available now and updates constantly—log
            your needs for faster matching.
          </CustomText>
        </Flex>

        {isLoaded ? (
          <div
            style={{
              position: "relative",
            }}
          >
            <GoogleMap
              options={options}
              mapContainerStyle={{
                width: "100%",
                height: "400px",
                borderRadius: 20,
              }}
              center={userLocation}
              zoom={6}
              onLoad={(map) => {
                onLoad(map);
              }}
              onClick={() => {
                setSelected(null);
                setId(null);
              }}
              onIdle={() => {
                debouncedHandleBoundsChanged();
              }}
            >
              {/* Add markers or other map components */}

              <MarkerClusterer
                styles={[
                  {
                    url: "/assets/images/markerCluster.png", // Small cluster icon
                    height: 40,
                    width: 40,
                    textColor: "#fff",
                    textSize: 14,
                  },
                  {
                    url: "/assets/images/markerCluster.png", // Medium cluster icon
                    height: 50,
                    width: 50,
                    textColor: "#fff",
                    textSize: 14,
                  },
                  {
                    url: "/assets/images/markerCluster.png", // Large cluster icon
                    height: 50,
                    width: 50,
                    textColor: "#fff",
                    textSize: 14,
                  },
                ]}
              >
                {(clusterer) => (
                  <>
                    {markers?.length
                      ? markers?.map((item, index: number) => {
                          return (
                            <Marker
                              key={item?.id}
                              position={{
                                lat: Number(item?.lat),
                                lng: Number(item?.lng),
                              }}
                              clusterer={clusterer}
                              icon={
                                __DEV__
                                  ? // ? MarkerPin
                                    {
                                      url: "https://images.ctfassets.net/3prze68gbwl1/assetglossary-17su9wok1ui0z7w/c4c4bdcdf0d0f86447d3efc450d1d081/map-marker.png",
                                      scaledSize: new window.google.maps.Size(
                                        40,
                                        40
                                      ),

                                      // fillColor: 'red',
                                    }
                                  : {
                                      url: "/assets/images/MapIcon.svg",
                                      scaledSize: new window.google.maps.Size(
                                        40,
                                        40
                                      ),

                                      // fillColor: 'red',
                                    }
                              }
                              onMouseOver={() => {
                                setSelected(item);
                                setId(index);
                              }}
                              onClick={() => {
                                setSelected(item);
                                setId(index);
                              }}
                            >
                              {selected && id === index && (
                                <CustomMarker
                                  selected={selected}
                                  setId={setId}
                                  setSelected={setSelected}
                                />
                              )}
                            </Marker>
                          );
                        })
                      : null}
                  </>
                )}
              </MarkerClusterer>
            </GoogleMap>

            {isLoading && (
              <Loader
                type="dots"
                size={32}
                style={{
                  position: "absolute",

                  zIndex: 2,
                  background: "#FFF",
                  top: "5%",
                  right: "50%",

                  transform: "translate(50%, 50%)",
                  width: 80,
                  borderRadius: 20,
                  boxShadow: `rgba(0, 0, 0, 0.35) 0px 5px 15px`,
                }}
              />
            )}
          </div>
        ) : (
          <Loader
            size={28}
            style={{
              position: "absolute",
              top: "70%",
              left: "50%",
              transform: "translate(50%, 0)",
              // zIndex: 2,
            }}
          />
        )}
      </Container>
    </div>
  ) : null;
};

export default SectionSix;
