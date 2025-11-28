import React, {
  HTMLInputTypeAttribute,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { closeNotification, notification } from "./notification";
import axios from "axios";
import { useAppSelector } from "@/store/hooks";
import { getCreditReportDomain } from "./createIconUrl";
import { modals } from "@mantine/modals";
import { Button, TextInput } from "@mantine/core";

function useCreditReportGenerate() {
  const userDetail = useAppSelector((state) => state.userReducer?.userDetail);
  const isCreditReportAvailable = useMemo(
    () => userDetail?.credit_report?.url && userDetail?.credit_report?.status,
    [userDetail]
  );

  const valueRef = useRef({
    applicant_first_name:
      getCreditReportDomain() !== "pocket-property-staging"
        ? "John"
        : userDetail?.name?.split(" ")[0],
    applicant_last_name:
      getCreditReportDomain() !== "pocket-property-staging"
        ? "Smith"
        : userDetail?.name?.split(" ").slice(1).join(" "),
    applicant_email_address:
      getCreditReportDomain() !== "pocket-property-staging"
        ? "john.smith@example.com"
        : userDetail?.email,
    applicant_mobile_number:
      getCreditReportDomain() !== "pocket-property-staging"
        ? "0766542813"
        : userDetail?.phone,
  });

  const updateValueRef = () => {
    if (userDetail) {
      valueRef.current = {
        applicant_first_name:
          getCreditReportDomain() === "pocket-property-staging"
            ? "John"
            : userDetail?.name?.split(" ")[0],
        applicant_last_name:
          getCreditReportDomain() === "pocket-property-staging"
            ? "Smith"
            : userDetail?.name?.split(" ").slice(1).join(" "),
        applicant_email_address:
          getCreditReportDomain() === "pocket-property-staging"
            ? "john.smith@example.com"
            : userDetail?.email,
        applicant_mobile_number:
          getCreditReportDomain() === "pocket-property-staging"
            ? "0766542813"
            : userDetail?.phone,
      };
    }
  };

  useEffect(() => {
    updateValueRef();
  }, [userDetail]);

  const onHandleChange = (event: any) => {
    const { value, name } = event?.target;
    valueRef.current = {
      ...valueRef.current,
      [name]: value,
    };
  };

  const handleSubmit = useCallback(async () => {
    try {
      notification({
        type: "loading",
        title: "Loading Credit Report",
        message: "Redirecting to Credit Report",
        autoClose: false,
      });
      const response = await axios.post(
        "https://api.getverified.co.za/api/v1/records",
        {
          data: valueRef?.current,
          metadata: {},
        }
      );

      window.location.href = `https://secure.getverified.co.za/${getCreditReportDomain()}/${
        response.data.id
      }`;
    } catch (error) {
      closeNotification();
      notification({
        type: "error",
        title: "Error",
        message: "Failed to fetch credit report",
      });
    }
  }, [valueRef]);
  const openModal = () => {
    modals.open({
      centered: true,
      title: "Get Credit Report",
      children: (
        <>
          <TextInput
            label="First Name"
            placeholder="Please enter your first name"
            data-autofocus
            mb={"sm"}
            name="applicant_first_name"
            onChange={onHandleChange}
            defaultValue={valueRef?.current?.applicant_first_name}
          />
          <TextInput
            label="Last Name"
            placeholder="Please enter your last name"
            data-autofocus
            mb={"sm"}
            onChange={onHandleChange}
            defaultValue={valueRef?.current?.applicant_last_name}
            name="applicant_last_name"
          />
          <TextInput
            label="Email"
            placeholder="Please enter your email name"
            disabled
            data-autofocus
            mb={"sm"}
            value={valueRef?.current?.applicant_email_address}
            name="applicant_email_address"
          />
          <TextInput
            label="Phone Number"
            placeholder="Please enter your phone numebr"
            disabled
            data-autofocus
            mb={"sm"}
            value={valueRef?.current?.applicant_mobile_number}
            name="applicant_mobile_number"
          />
          <Button fullWidth onClick={handleSubmit} mt="md">
            Submit
          </Button>
        </>
      ),
      onClose: () => {
        updateValueRef();
      },
    });
  };

  const generateCreditReport = async () => {
    try {
      if (userDetail?.credit_report?.url && userDetail?.credit_report?.status) {
        window.open(userDetail?.credit_report?.url, "_blank");

        return;
      } else {
        openModal();
      }
    } catch (error) {
      closeNotification();
      notification({
        type: "error",
        title: "Error",
        message: "Failed to fetch credit report",
      });
    }
  };
  return { generateCreditReport, isCreditReportAvailable };
}

export default useCreditReportGenerate;
