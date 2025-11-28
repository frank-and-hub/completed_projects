import { AxiosRequestConfig } from "axios";
import { post_api, post_form_data } from "../root_apis/root_api";

interface subscriptionParamsType {
  subscription_id: string;
  amount?: string;
}
const subscription = (data: subscriptionParamsType) =>
  post_api("subscription", { ...data });

const checkAmountZero = ({ subscription_id }: { subscription_id: string }) =>
  post_api(`free-plan/${subscription_id}`);

const transactionHistory = () => post_api("transcation-history");
const matchedProperty = (data: {
  start_date: string;
  end_date: string;
  page: string;
}) =>
  post_api(
    "user-metching-property",
    { start_date: data?.start_date, end_date: data?.end_date },
    {
      params: {
        page: Number(data?.page),
      },
    }
  );

const uploadContract = (
  data: FormData,
  headers?: AxiosRequestConfig<Headers>
) => post_form_data("tenant_upload_contract", data, headers);

export {
  subscription,
  transactionHistory,
  matchedProperty,
  checkAmountZero,
  uploadContract,
};
