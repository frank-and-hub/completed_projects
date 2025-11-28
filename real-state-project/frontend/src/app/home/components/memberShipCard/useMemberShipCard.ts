import { checkSubscription, planAmount } from "@/api/plans/plan";
import { searchFilter } from "@/api/search/searchFilter";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import {
  planAmountQueryKey,
  propertyNeedsQueryKey,
} from "@/utils/queryKeys/planAmountQueryKey";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useSearchParams } from "next/navigation";
import { useEffect, useMemo, useState } from "react";

const useMemberShipCard = ({
  isFromSearchFilter,
}: {
  isFromSearchFilter: boolean;
  handleClose?: () => void;
  planApiAmountData?: planAmountType;
}) => {
  const { token, isPropertySearch, propertySearchData, userDetail } =
    useAppSelector((state) => state?.userReducer);

  const dispatch = useAppDispatch();
  const queryClient = useQueryClient();
  const [id, setId] = useState<string>("");
  const [isNewModalOpen, setIsNewModalOpen] = useState<string>("");
  const { data, isError } = useQuery<planAmountType, Error>({
    queryKey: [...planAmountQueryKey.list],
    queryFn: () => planAmount(),
  });

  const { setContextValue, setIsModalOpen } = useGlobalContext();
  const { mutate: searchMutate } = useMutation({
    mutationFn: searchFilter,
    onSuccess: async () => {
      try {
        const currentDate = new Date();
        const fifteenDaysAgo = new Date(currentDate);
        fifteenDaysAgo.setDate(currentDate.getDate() - 10);
        await queryClient.invalidateQueries({
          queryKey: [...profileQueryKey.list],
        });
        await queryClient.invalidateQueries({
          queryKey: [
            ...propertyNeedsQueryKey.list,
            fifteenDaysAgo,
            currentDate,
          ],
        });
        setContextValue((prev: any) => ({
          ...prev,
          isSearchApiCall: false,
        }));
        notification({
          message: "Your request has been submitted.",
        });

        dispatch(updatePropertyInformation(undefined));
        dispatch(updatePropertySearch(false));
      } catch (err) {
        console.log({ err });
      }
    },
  });
  const searchParams = useSearchParams();
  const search = searchParams?.get("payment");
  useEffect(() => {
    if (search === "success") {
      setIsModalOpen("planThank");
      if (isPropertySearch) {
        searchMutate({
          ...propertySearchData,
        });
      }
    }
  }, [search]);

  const planAmountData = useMemo(() => {
    type key = "tenant" | "privatelandlord" | "agency";
    const obj: {
      [key: string]: {
        amount: string;
        id: string;
        plan_name: string;
        type: string;
      }[];
    } = {};
    data?.data?.map((item) => {
      if (obj[item?.type]) {
        obj[item?.type].push({
          amount: item?.amount,
          id: item?.id,
          plan_name: item?.plan_name,
          type: item?.type,
        });
      } else {
        obj[item?.type] = [
          {
            amount: item?.amount,
            id: item?.id,
            plan_name: item?.plan_name,
            type: item?.type,
          },
        ];
      }
    });
    return obj;
  }, [data]);

  const { mutate: check_subscription, isPending: check_subscriptionLoading } =
    useMutation({
      mutationFn: checkSubscription,
      onSuccess: (data) => {
        setIsModalOpen("payment");
        if (isFromSearchFilter) {
          dispatch(updatePropertySearch(true));
        } else if (!isFromSearchFilter) {
          dispatch(updatePropertySearch(false));
          dispatch(updatePropertyInformation(undefined));
        }
      },
    });
  const subscribe = (id: string, amount: string) => {
    if (token) {
      setId(id);
      setContextValue((prev: any) => ({
        ...prev,
        subscriptionId: id,
        amount,
      }));
      check_subscription();
    } else {
      setIsNewModalOpen("true");
    }
  };
  return {
    planAmountData,
    subscribe,
    id,
    isNewModalOpen,
    setIsNewModalOpen,
    setContextValue,
    setIsModalOpen,
    check_subscriptionLoading,
  };
};

export default useMemberShipCard;
