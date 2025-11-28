import { getRequestList } from "@/api/request/request";
import { matchedProperty } from "@/api/subscription/subscription";
import { useAppSelector } from "@/store/hooks";
import {
  matchedPropertyQueryKey,
  transactionQueryKey,
} from "@/utils/queryKeys/transactionHistoryKeys";
import { useForm } from "@mantine/form";
import { useQuery } from "@tanstack/react-query";
import dayjs from "dayjs";
import { useSearchParams } from "next/navigation";
import { useRouter } from "next/navigation";
import { useEffect, useMemo } from "react";

const useRequest = () => {
  const router = useRouter();
  const { token } = useAppSelector((state) => state?.userReducer);

  const currentDate = new Date();
  const fifteenDaysAgo = new Date(currentDate);
  fifteenDaysAgo.setDate(currentDate.getDate() - 10);
  const searchParams = useSearchParams();
  const startDate =
    searchParams?.get("start_date") ??
    String(dayjs(fifteenDaysAgo).format("YYYY-MM-DD"));
  const endDate =
    searchParams?.get("end_date") ??
    String(dayjs(currentDate).format("YYYY-MM-DD"));
  let page = searchParams?.get("page");

  const form = useForm<{ start_date: Date; end_date: Date }>({
    initialValues: {
      start_date: startDate ? new Date(startDate) : fifteenDaysAgo,
      end_date: endDate ? new Date(endDate) : currentDate,
    },
  });
  const { end_date, start_date } = form.values;
  const queryKey = [...matchedPropertyQueryKey.list, endDate, startDate, page];
  const { data, isLoading, isPending } = useQuery({
    queryKey,
    queryFn: () =>
      getRequestList({
        end_date: dayjs(endDate).format("YYYY-MM-DD"),
        start_date: dayjs(startDate).format("YYYY-MM-DD"),
        page: page ?? "1",
      }),
    enabled: !!token && !!startDate && !!endDate,
  });
  function daysBetweenDates(date1: any, date2: any) {
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);

    const differenceInTime = secondDate.getTime() - firstDate.getTime();

    const differenceInDays = differenceInTime / (1000 * 3600 * 24);

    return Math.abs(differenceInDays);
  }
  useEffect(() => {
    const params = new URLSearchParams();
    if (start_date && end_date) {
      params.set("start_date", String(dayjs(start_date).format("YYYY-MM-DD")));
      params.set("end_date", String(dayjs(end_date).format("YYYY-MM-DD")));
    } else {
      params.set(
        "start_date",
        String(dayjs(fifteenDaysAgo).format("YYYY-MM-DD"))
      );
      params.set("end_date", String(dayjs(currentDate).format("YYYY-MM-DD")));
    }

    router.push(`?${params.toString()}`);
  }, [start_date, end_date]);
  const totalDays = useMemo(() => {
    if (start_date && end_date) {
      const daysBetween = daysBetweenDates(start_date, end_date);
      return daysBetween ?? 1;
    } else {
      const daysBetween = daysBetweenDates(fifteenDaysAgo, currentDate);
      return daysBetween ?? 1;
    }
  }, [start_date, end_date]);

  const onPageSet = (page: number) => {
    const params = new URLSearchParams();
    params.set("start_date", String(dayjs(start_date).format("YYYY-MM-DD")));
    params.set("end_date", String(dayjs(end_date).format("YYYY-MM-DD")));
    params.set("page", String(page));
    router.push(`?${params.toString()}`);
  };

  return {
    data,
    isLoading: isLoading || isPending,
    totalDays,
    form,
    queryKey,
    onPageSet,
    page,
  };
};
export default useRequest;
