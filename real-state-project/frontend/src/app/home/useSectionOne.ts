import { useDebounceMap } from "@/utils/useDebounceMap";
import axios from "axios";
import { useEffect, useRef, useState } from "react";
import { useJsApiLoader } from "@react-google-maps/api";
import { useGlobalContext } from "@/utils/context";
import { id } from "intl-tel-input/i18n";
import { getCountryCityDataByName } from "@/api/propertySearchHistory/propertySearch";
import { closeNotification, notification } from "@/utils/notification";
// import { google } from '@google/maps';

const useSectionOne = () => {
  const placeInputRef = useRef<HTMLInputElement>(null);

  const { setContextValue, setIsModalOpen } = useGlobalContext();
  const GOOGLE_MAP_API_KEY = "AIzaSyCLXi82PTPRmZSgvRgJSDD6wtgO5MzHxpk";
  // const GOOGLE_MAP_API_KEY = 'AIzaSyB5wIXS_zOIQuz24pELj0_MqfyaFgdPvhM';
  const [search, setSearch] = useState<string>("");
  useEffect(() => {
    window.addEventListener("new-event", () => {
      if (placeInputRef.current) {
        placeInputRef.current.value = "";
      }
    });
  }, []);
  const onChangeText = async () => {
    if (search.trim() === "") {
      setSearch("");
      return;
    }

    // const apiUrl = `${GOOGLE_PACES_API_BASE_URL}/autocomplete/json?key=${GOOGLE_MAP_API_KEY}&input=${search.term}&components=country:iq&sensor=false`;
    const apiUrl = `${GOOGLE_PACES_API_BASE_URL}/autocomplete/json?key=${GOOGLE_MAP_API_KEY}&input=${search}`;

    try {
      const result = await axios.request({
        method: "post",
        url: apiUrl,
      });

      if (result) {
        setSearch("");
      }
    } catch (err: any) {
      if (err.message === "Network Error") {
      } else {
        if (err.message) {
          console.log(err);
        }
      }
    }
  };

  useDebounceMap(onChangeText, 1000, [search]);
  const onHandleSearch = (text: string) => {
    setSearch(text);
  };
  const { isLoaded, loadError } = useJsApiLoader({
    googleMapsApiKey: "AIzaSyCLXi82PTPRmZSgvRgJSDD6wtgO5MzHxpk",
    libraries: ["places"],
    id: "google-map-script",
  });
  useEffect(() => {
    map();
  }, [isLoaded]);

  const [autoComplete, setAutoComplete] =
    useState<google.maps.places.Autocomplete | null>();
  useEffect(() => {
    const fetchAutoComplete = async () => {
      if (autoComplete) {
        autoComplete.addListener("place_changed", async () => {
          const place = autoComplete.getPlace();
          const { address_components } = place;
          console.log(address_components);

          let obj: any = { CityName: "", provinceName: "", SuburbName: "" };
          address_components?.forEach((item) => {
            if (
              item?.types?.includes("sublocality") ||
              item?.types?.includes("sublocality_level_1")
            ) {
              obj["SuburbName"] = item?.long_name;
              obj["suburb_name"] = item?.long_name;
            }
            if (item?.types?.includes("administrative_area_level_1")) {
              obj["provinceName"] = item?.long_name;
              obj["province_name"] = item?.long_name;
            }
            if (item?.types?.includes("locality")) {
              obj["CityName"] = item?.long_name;
              obj["city"] = item?.long_name;
            }
            if (item?.types?.includes("country")) {
              obj["countryName"] = item?.long_name;
              obj["country_name"] = item?.long_name;
            }
          });

          notification({
            type: "loading",
            message: "Fetching Location...",
          });

          const res = await getCountryCityDataByName({
            city: replaceSpecialCharsWithSpace(obj?.city),
            country: replaceSpecialCharsWithSpace(obj?.country_name),
            province: replaceSpecialCharsWithSpace(obj?.province_name),
            suburb: replaceSpecialCharsWithSpace(obj?.suburb_name),
          });

          await setContextValue((prev: contextValuesType) => ({
            ...prev,
            ...obj,
            cityId: res?.data?.city?.id,
            country_Id: res?.data?.country?.id,
            province_Id: res?.data?.province?.id,
            suburbId: res?.data?.suburb?.id,
            currency: res?.data?.country?.currency,
            propertySearchData: {
              country_name: res?.data?.country?.label,
              suburb_name: res?.data?.suburb?.label,
              province_name: res?.data?.province?.label,
              city: res?.data?.city?.label,
              currency: res?.data?.country?.currency,
            },
          }));
          closeNotification();
          setIsModalOpen("advanceFilter");
        });
      }
    };
    fetchAutoComplete();
  }, [autoComplete]);
  function replaceSpecialCharsWithSpace(input?: string): string {
    return !input ? "" : input.replace(/[^a-zA-Z0-9]/g, " ");
  }
  const map = async () => {
    try {
      const southAfricaBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng({ lat: 22.1306, lng: 31.053 }),
        new google.maps.LatLng({ lat: 34.3572, lng: 18.4731 })
      );
      const gAutoComplete = new google.maps.places.Autocomplete(
        placeInputRef.current as HTMLInputElement,
        {
          bounds: southAfricaBounds,
          componentRestrictions: { country: ["za"] },
        }
      );
      setAutoComplete(gAutoComplete);

      // let res = await axios.get(
      //   `https://maps.googleapis.com/maps/api/place/autocomplete/json?key=AIzaSyCLXi82PTPRmZSgvRgJSDD6wtgO5MzHxpk&input=jaipur`,
      //   {
      //     headers: {
      //       'Content-Type': 'application/json',
      //       'Access-Control-Allow-Origin': 'http://localhost:3000',
      //       Accept: 'application/json',
      //     },
      //   }
      // );
      // const response = await axios.get(
      //   `https://maps.googleapis.com/maps/api/place/details/json`,
      //   {
      //     params: {
      //       place_id: 'ChIJNxSHd3O2bTkRa-fqlAsbGW0',
      //       key: 'AIzaSyCLXi82PTPRmZSgvRgJSDD6wtgO5MzHxpk',
      //     },

      //     headers: {
      //       'Content-Type': 'application/json',
      //       'Access-Control-Allow-Origin': 'http://localhost:3000',
      //       Accept: 'application/json',
      //     },
      //   }
      // );
      // console.log('Map', { res });
      // return res;
    } catch (error) {}
  };

  return { onHandleSearch, placeInputRef };
};

export default useSectionOne;

export const GOOGLE_PACES_API_BASE_URL =
  "https://maps.googleapis.com/maps/api/place";
