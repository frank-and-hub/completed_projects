import { searchFilterHistory } from "@/api/propertySearchHistory/propertySearch";
import { useAppSelector } from "@/store/hooks";
import { propertyNeedsQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { useForm } from "@mantine/form";
import { useMutation, useQuery } from "@tanstack/react-query";
import dayjs from "dayjs";
import { useRouter, useSearchParams } from "next/navigation";
import { useEffect, useMemo } from "react";

const usePropertyNeeds = () => {
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
  const form = useForm<{ start_date: Date; end_date: Date }>({
    initialValues: {
      start_date: startDate ? new Date(startDate) : fifteenDaysAgo,
      end_date: endDate ? new Date(endDate) : currentDate,
    },
  });
  const { end_date, start_date } = form.values;
  const {
    isLoading,
    data: propertyNeedsList,
    isPending,
  } = useQuery<propertyNeedsType>({
    queryKey: [...propertyNeedsQueryKey.list, startDate, endDate],
    queryFn: () =>
      searchFilterHistory({
        end_date: dayjs(endDate).format("YYYY-MM-DD"),
        start_date: dayjs(startDate).format("YYYY-MM-DD"),
      }),
    enabled: !!token && !!startDate && !!endDate,
  });

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
  function daysBetweenDates(date1: any, date2: any) {
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);

    const differenceInTime = secondDate.getTime() - firstDate.getTime();

    const differenceInDays = differenceInTime / (1000 * 3600 * 24);

    return Math.abs(differenceInDays);
  }
  const totalDays = useMemo(() => {
    if (start_date && end_date) {
      const daysBetween = daysBetweenDates(start_date, end_date);
      return daysBetween ?? 1;
    } else {
      const daysBetween = daysBetweenDates(fifteenDaysAgo, currentDate);
      return daysBetween ?? 1;
    }
  }, [start_date, end_date]);
  return {
    isPending: isLoading || isPending,
    form,
    propertyNeedsList,
    totalDays,
  };
};

export default usePropertyNeeds;
