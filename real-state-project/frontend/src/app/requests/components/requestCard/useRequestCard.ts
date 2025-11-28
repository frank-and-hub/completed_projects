import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { useGlobalContext } from "@/utils/context";

function useRequestCard() {
  const { userDetail } = useAppSelector((state) => state?.userReducer);
  const dispatch = useAppDispatch();
  const { setIsModalOpen, setContextValue } = useGlobalContext();

  return { userDetail, dispatch, setContextValue };
}

export default useRequestCard;
