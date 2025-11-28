import { searchFilter } from "@/api/search/searchFilter";
import { checkAmountZero, subscription } from "@/api/subscription/subscription";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import { propertyNeedsQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useEffect } from "react";

const usePayment = (handleClose: any) => {
  const queryClient = useQueryClient();
  const { contextValue, setIsModalOpen } = useGlobalContext();

  const {
    propertySearchData: propertySearchFromContext,
    isSearchApiCall,
    amount,
    subscriptionId,
  } = contextValue;

  const { mutate: searchMutate, isPending: searchLoading } = useMutation({
    mutationFn: searchFilter,
    onSuccess: async (data) => {
      try {
        if (data?.total_request === 5) {
          await queryClient.invalidateQueries({
            queryKey: [...profileQueryKey.list],
          });
        }
        notification({
          message: "Your request has been submitted.",
        });

        setIsModalOpen("thankYou");
        // handleClose && handleClose();
      } catch (err) {
        console.error(err);
      }
    },
  });
  const { isPending, data } = useQuery<paymentDataType, Error>({
    queryKey: ["paymentDetail", subscriptionId],
    queryFn: () => subscription({ subscription_id: subscriptionId }),
    enabled: !!subscriptionId,
  });

  const { isPending: amountLoading, mutate } = useMutation({
    mutationFn: checkAmountZero,
    onSuccess: async (data) => {
      notification({
        message: "Your have successfully purchased free plan.",
      });
      await queryClient.invalidateQueries({
        queryKey: [...profileQueryKey.list],
      });

      if (isSearchApiCall) {
        searchMutate({
          ...propertySearchFromContext,
        });
        window.dispatchEvent(new Event("new-event"));
      } else {
        setIsModalOpen("");

        handleClose && handleClose();
      }
    },
  });
  return {
    isPending: isPending || searchLoading,
    data,
    amount,
    amountLoading,
    mutate,
    subscriptionId,
  };
};

export default usePayment;
