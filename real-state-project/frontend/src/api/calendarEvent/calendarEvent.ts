import { get_api, get_api_data } from '../root_apis/root_api';

const getCalendarEventList = ({
  page = 1,
  is_upcoming,
}: {
  page?: number;
  is_upcoming: 0 | 1;
}): Promise<paginationDataType<calendarEventListItemType>> =>
  get_api_data('calendar', { params: { page, is_upcoming } });

export { getCalendarEventList };
