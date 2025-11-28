import CustomButton from "@/components/customButton/CustomButton";
import { useGlobalContext } from "@/utils/context";
import { Box } from "@mantine/core";
import { IconInfoCircle } from "@tabler/icons-react";
import "./LimitAlert.scss";
type Props = {};

const MaximumLimitAlert = ({ handleClose }: { handleClose?: () => void }) => {
  const { setIsModalOpen } = useGlobalContext();
  return (
    <div className="inner_info_modal">
      <h2>
        <IconInfoCircle stroke={1.25} /> Important Information
      </h2>
      <p>
        You have reached the maximum limit for raising request. Please buy a new
        plan to continue
      </p>

      <Box className="inner_info_modal_btns">
        <CustomButton
          className="btn_cancel"
          onClick={() => {
            handleClose && handleClose();
          }}
        >
          CANCEL
        </CustomButton>

        <CustomButton
          onClick={() => {
            handleClose && handleClose();
            setIsModalOpen("selectPlan");
          }}
        >
          Continue
        </CustomButton>
      </Box>
    </div>
  );
};
export default MaximumLimitAlert;
