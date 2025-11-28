import { logOut } from "@/api/auth/login";
import { useAppDispatch } from "@/store/hooks";
import { useGlobalContext } from "@/utils/context";
import { Button, Text } from "@mantine/core";
import { modals } from "@mantine/modals";
import { useMutation } from "@tanstack/react-query";
import { useRouter } from "next/navigation";

const useHeader = ({
  toggleDesktop,
  desktopOpened,
  mobileOpened,
  toggleMobile,
}: {
  toggleDesktop: any;
  toggleMobile: any;
  mobileOpened: boolean;
  desktopOpened: boolean;
}) => {
  const router = useRouter();
  const { setIsModalOpen } = useGlobalContext();
  const dispatch = useAppDispatch();
  const { mutate } = useMutation({
    mutationFn: logOut,

    onSuccess: () => {
      setIsModalOpen("logout");
      dispatch({ type: "LOGOUT" });
      router.push("/");
      if (mobileOpened) {
        toggleMobile();
      } else if (desktopOpened) {
        toggleDesktop();
      }
    },
  });
  const onLogout = () => {
    modals.openConfirmModal({
      title: "Are you sure you want to log out?",

      children: <Text size="xs">Your current session will end. Proceed?</Text>,
      labels: { confirm: "Logout", cancel: "Cancel" },
      onCancel: () => console.log("Cancel"),
      onConfirm: mutate,
      centered: true,
    });
  };
  return { onLogout };
};

export default useHeader;
