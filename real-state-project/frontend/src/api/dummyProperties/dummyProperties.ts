import { axiosInstance } from "../root_apis/axiosInstance.service";
import { get_api, get_api_data } from "../root_apis/root_api";

const getDummyProperties = (): Promise<propertyDetailItemType[]> =>
  get_api_data(`demo-properties`);
const getDummyPropertyById = ({ propertyId }: { propertyId: string }) =>
  get_api_data(`demo-properties/${propertyId}`);

export { getDummyProperties, getDummyPropertyById };
