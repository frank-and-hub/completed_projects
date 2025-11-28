import { get_api, get_api_data, post_api } from "../root_apis/root_api";

interface propertySearchHistoryParamsType {
  start_date: string;
  end_date: string;
}
const searchFilterHistory = (data: propertySearchHistoryParamsType) =>
  post_api("property-needs", { ...data });

const provinceLists = () => get_api_data("province");

const cityList = (id: string) => get_api_data(`city/${id}`);

const suburbList = (id: string) => get_api_data(`suburb/${id}`);
const propertyDetail = (id: string) => get_api_data(`property-details/${id}`);

const propertyDetailMap = ({ id }: { id: string }) =>
  get_api_data(`internal-property-details/${id}`);

const propertyEvevntDetailMap = ({ id }: { id: string }) =>
  get_api_data(`calendar-details/${id}`);

const messageSchedule = (data: {
  start_time: string;
  end_time: string;
  schedule_type: string;
}) => post_api("set-message-schedule-time", data);
const propertyEnquiry = (data: {
  email: string;
  property_id: string;
  phone: string;
  full_name: string;
  message: string;
}) => post_api("sent-client-mail", data);

const getCountryCityDataByName = (data: {
  country: string;
  suburb: string;
  province: string;
  city: string;
}) => post_api("advanced-filter", data);

const getAllCountries = ({
  page = 1,
  search,
}: {
  page?: number;
  search?: string;
}) => get_api_data("countries", { params: { page, search } });

const getProvinceList = ({
  countryId,
  page,
  search,
}: {
  countryId: string;
  page?: number;
  search?: string;
}) => get_api_data(`states/${countryId}`, { params: { page, search } });

const getCityList = ({
  provinceId,
  page,
  search,
}: {
  provinceId: string;
  page?: number;
  search?: string;
}) => get_api_data(`cities/${provinceId}`, { params: { page, search } });
const getSuburbList = ({
  page,
  search,
  cityId,
}: {
  cityId: string;
  page?: number;
  search?: string;
}) => get_api_data(`suburbs/${cityId}`, { params: { page, search } });

const getPropertySearchData = () => get_api_data("columns");

const getPropertyCount = (): Promise<{
  [key in string]: number;
}> =>
  get_api(`top-city-rent-count`).then(
    (res: { total: number; town: string }[]) => {
      let obj = {};
      if (res?.length) {
        res?.map((item) => {
          obj = {
            ...obj,
            [slugGenerator(item?.town)]: item?.total,
          };
        });
      }

      return obj;
    }
  );

const slugGenerator = (str: string) => {
  return !str
    ? ""
    : str
        .toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w-]+/g, "");
};

export {
  searchFilterHistory,
  provinceLists,
  cityList,
  suburbList,
  messageSchedule,
  propertyDetail,
  propertyEnquiry,
  getAllCountries,
  getProvinceList,
  getCityList,
  getPropertySearchData,
  propertyDetailMap,
  getSuburbList,
  getPropertyCount,
  getCountryCityDataByName,
  propertyEvevntDetailMap,
};
