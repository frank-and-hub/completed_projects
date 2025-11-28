import { getProfile } from "@/api/auth/profile";
import { messageAlert } from "@/api/search/searchFilter";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { updateUserInformation } from "@/store/reducer/userReducer";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useEffect } from "react";

const useNavBar = () => {
  const dispatch = useAppDispatch();
  const { token, userDetail } = useAppSelector((state) => state.userReducer);
  const { data, isPending, isError, isRefetching } = useQuery<
    ProfileDetailType,
    Error
  >({
    queryKey: [...profileQueryKey.list],
    queryFn: () => getProfile(),
    enabled: !!token,
  });
  const { mutate } = useMutation({
    mutationFn: messageAlert,
    onSuccess: (data) => {
      // notification({
      //   message:
      //     data?.message_alert === 1
      //       ? 'Your WhatsApp message alerts are enabled'
      //       : 'Your WhatsApp message alerts are disabled',
      // });
    },
  });
  useEffect(() => {
    if (data) {
      dispatch(updateUserInformation(data));
    }
  }, [data, isRefetching]);
  const alertHandler = (value: boolean) => {
    mutate({ message_alert: value ? 1 : 0 });
  };
  return { isPending, data, userDetail, token, alertHandler };
};
export default useNavBar;
