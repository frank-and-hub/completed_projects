import CustomButton from "@/components/customButton/CustomButton";
import React from "react";
import ModalCloseIcon from "../home/components/modalCloseIcon/ModalCloseIcon";
import LogoutImg from "../../../assets/svg/logout.svg";
import Image from "next/image";
import "./logOut.scss";

function LogOut({ handleClose }: any) {
  return (
    <div className="inner_logout_modal">
      <div className="modal_head_close">
        <ModalCloseIcon
          handleClose={() => {
            handleClose();
            window.location.reload();
          }}
        />
      </div>
      <div className="inner_logout_modal_in">
        <Image src={LogoutImg} alt="" />
        <h4>Logged out successfully.</h4>
        <CustomButton
          onClick={() => {
            handleClose();
            window.location.reload();
          }}
        >
          Home
        </CustomButton>
      </div>
    </div>
  );
}

export default LogOut;
