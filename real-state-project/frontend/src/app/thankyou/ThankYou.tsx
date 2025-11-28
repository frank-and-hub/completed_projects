import CustomButton from "@/components/customButton/CustomButton";
import React from "react";
import "./thankyou.scss";
import thankyouImg from "../../../assets/svg/thankyou_img.svg";
import Image from "next/image";
import CustomText from "@/components/customText/CustomText";
import { useGlobalContext } from "@/utils/context";
import { useAppSelector } from "@/store/hooks";
import { useRouter } from "next/navigation";

function ThankYou({
  handleClose,
  title,
  description,
}: {
  handleClose?: () => void;
  title: string;
  description?: string;
}) {
  const { setIsModalOpen } = useGlobalContext();
  const { userDetail } = useAppSelector((state) => state?.userReducer);
  const router = useRouter();
  return (
    <div className="thankyou_page">
      <Image src={thankyouImg} alt="no image" />
      <h4>{title}</h4>
      {description ? <CustomText my={"md"}>{description}</CustomText> : null}

      {!description &&
      userDetail?.subscription_type === "Basic" &&
      !userDetail?.schedule_type ? (
        <CustomButton
          mt={!description ? "md" : undefined}
          onClick={() => {
            handleClose && handleClose();
            setIsModalOpen("messageAlert");
          }}
        >
          Select time slot
        </CustomButton>
      ) : (
        <CustomButton
          mt={!description ? "md" : undefined}
          onClick={() => {
            if (
              description ||
              !(
                userDetail?.subscription_type === "Basic" &&
                !userDetail?.schedule_type
              )
            ) {
              router.push("/");
              handleClose && handleClose();
            }
          }}
        >
          Home
        </CustomButton>
      )}
    </div>
  );
}

export default ThankYou;
