"use client";
import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import { searchFilter } from "@/api/search/searchFilter";
import { useAppSelector } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import React, { useEffect } from "react";
import { useDispatch } from "react-redux";

function RenderHook() {
  const queryClient = useQueryClient();

  const dispatch = useDispatch();
  const { mutate: searchMutate, isPending: searchLoading } = useMutation({
    mutationFn: searchFilter,
    onSuccess: async (data) => {
      try {
        if (data?.total_request === 5) {
          await queryClient.invalidateQueries({
            queryKey: [...profileQueryKey.list],
          });
        }
        dispatch(updatePropertyInformation(undefined));
        dispatch(updatePropertySearch(false));
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
  const { userDetail } = useAppSelector((state) => state?.userReducer);
  const { setIsModalOpen } = useGlobalContext();
  useEffect(() => {
    console.log("userDetail", userDetail);

    if (
      userDetail?.login_type === "microsoft" ||
      (userDetail?.login_type === "google" && !userDetail?.phone) ||
      (userDetail?.name &&
        (!userDetail?.user_employment?.emplyee_type ||
          !userDetail?.user_employment?.live_with))
    ) {
      setIsModalOpen("authModal");
    }
  }, []);

  return null;
}

export default RenderHook;
