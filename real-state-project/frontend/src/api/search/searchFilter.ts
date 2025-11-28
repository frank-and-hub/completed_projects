import { post_api } from '../root_apis/root_api';

const searchFilter = (data: searchFilterParamsType) =>
  post_api('search-property', { ...data });
const messageAlert = ({ message_alert }: { message_alert: number }) =>
  post_api('message-alert', { message_alert });

export { searchFilter, messageAlert };
