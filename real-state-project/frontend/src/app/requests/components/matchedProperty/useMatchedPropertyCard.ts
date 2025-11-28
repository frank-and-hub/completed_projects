import {
  acceptPropertyInviteApi,
  declinePropertyInviteApi,
  // reshedulePropertyInviteApi,
} from "@/api/request/request";
import { notification } from "@/utils/notification";
import { useMutation, useQueryClient } from "@tanstack/react-query";

const useMatchedPropertyCard = () => {
  const queryClient = useQueryClient()
  const { mutate: acceptInviteMutate, isPending: acceptPending } = useMutation({
    mutationFn: acceptPropertyInviteApi,
    onSuccess: async (data) => {
      await queryClient.invalidateQueries({ queryKey: ["calendarEventList", "future"] })
      // console.log("acceptPropertyInviteApi", data);
      notification({
        message: "Your request has been Accepted.",
      });
    },
  });

  const { mutate: declineInviteMutate, isPending: declinePending } = useMutation({
    mutationFn: declinePropertyInviteApi,
    onSuccess: async (data) => {
      await queryClient.invalidateQueries({ queryKey: ["calendarEventList", "future"] })
      // console.log("declinePropertyInviteApi", data);
      notification({
        message: "Your request has been Declined.",
      });
    },
  });

  // const { mutate: rescheduleInviteMutate, isPending: reschedulePending } = useMutation({
  //     mutationFn: reshedulePropertyInviteApi,
  //     onSuccess: (data) => {
  //       console.log("reshedulePropertyInviteApi", data);
  //     },
  //   });

  return {
    declineInviteMutate,
    declinePending,
    acceptInviteMutate,
    acceptPending,
    // rescheduleInviteMutate,
    // reschedulePending,
  };
};
export default useMatchedPropertyCard;
