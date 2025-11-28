import { useAppDispatch } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import { ActionIcon } from "@mantine/core";
import { IconX } from "@tabler/icons-react";
import React from "react";

function ModalCloseIcon({
  handleClose,
  isFromAdvanceFilter,
}: {
  handleClose?: VoidFunction;
  isFromAdvanceFilter?: boolean;
}) {
  const dispatch = useAppDispatch();
  const { setContextValue } = useGlobalContext();
  const resetContextValues = () => {
    setContextValue((prev: contextValuesType) => ({
      ...prev,
      isSearchApiCall: false,
      propertySearchData: {},
      advanceFeatureData: {},
      country_Id: 0,
      suburbId: "",
      cityId: "",
      province_Id: "",
      currency: "",
      advanceFeatureSelectedData: [],
      requestAgainData: {},
    }));
    dispatch(updatePropertyInformation(undefined));
    dispatch(updatePropertySearch(false));
    window.dispatchEvent(new Event("new-event"));
  };

  return (
    <ActionIcon
      onClick={() => {
        handleClose && handleClose();
        if (isFromAdvanceFilter) {
          resetContextValues();
        }
      }}
      variant="light"
      className="close_icon_here"
      style={{ borderRadius: "50%" }}
    >
      <IconX stroke={2} />
    </ActionIcon>
  );
}

export default ModalCloseIcon;
