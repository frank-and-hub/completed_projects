import { messageSchedule } from "@/api/propertySearchHistory/propertySearch";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";

const useMessageAlert = ({ handleClose }: any) => {
  const [selectItem, setSelectItem] = useState<messageAlertDataType | null>(
    null
  );
  const queryClient = useQueryClient();
  const { isPending, mutate } = useMutation({
    mutationFn: messageSchedule,
    onSuccess: async () => {
      try {
        await queryClient.invalidateQueries({
          queryKey: [...profileQueryKey.list],
        });
        handleClose && handleClose();
        notification({
          message: "Your message schedule time set successfully.",
        });
      } catch (err) {
        console.log({ err });
      }
    },
  });
  return { isPending, mutate, selectItem, setSelectItem };
};

export default useMessageAlert;
