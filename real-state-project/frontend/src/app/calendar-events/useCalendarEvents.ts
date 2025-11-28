import { getCalendarEventList } from '@/api/calendarEvent/calendarEvent';
import { useInfiniteQuery, useQuery } from '@tanstack/react-query';
import { useState } from 'react';

const useCalendarEvents = () => {
  const [activeEvents, setActiveEvents] = useState<'past' | 'future'>('future');

  const eventListQuery = useInfiniteQuery({
    queryKey: ['calendarEventList', activeEvents],
    queryFn: ({ pageParam }) =>
      getCalendarEventList({
        page: pageParam,
        is_upcoming: activeEvents === 'past' ? 0 : 1,
      }),
    initialPageParam: 1,
    getNextPageParam: (lastPage) => {
      if (lastPage?.meta?.total_page > lastPage?.meta?.current_page) {
        return lastPage?.meta?.current_page + 1;
      } else {
        return undefined;
      }
    },
    enabled: !!activeEvents,
  });

  return { activeEvents, setActiveEvents, eventListQuery };
};

export default useCalendarEvents;
