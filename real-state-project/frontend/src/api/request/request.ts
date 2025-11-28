import { get_api, get_api_data, post_api } from "../root_apis/root_api";

const getRequestList = async (params: {
  start_date: string;
  end_date: string;
  page: string;
}): Promise<request_list_api> =>
  get_api_data(`property-request-all`, { params });

const getMatchedByRequestedId = async (params: {
  page: string;
  id: string;
}): Promise<propertyListItemByID[]> =>
  get_api(`property-request-data/${params?.id}`, { params });
const acceptPropertyInviteApi = (id: string) =>
  get_api(`invite/accept/${id}/${id}`);
const declinePropertyInviteApi = (id: string) =>
  post_api(`invite/decline/${id}/${id}`);
const shareCivilReport = (data: {
  user_id: string;
  search_id: string;
  status: "approved" | "unapproved";
  client_id: string;
  property_id: string;
  property_type: string;
}) => post_api(`property-request/report/status`, data);
const reshedulePropertyInviteApi = (id: string | null, data: {
  property_id: string | null;
  date: string;
  time: string;
  message: string;
}) => post_api(`invite/reschedule/${id}/${id}`, data);

export {
  getRequestList,
  getMatchedByRequestedId,
  shareCivilReport,
  acceptPropertyInviteApi,
  declinePropertyInviteApi,
  reshedulePropertyInviteApi,
};
