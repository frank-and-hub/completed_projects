import CustomButton from "@/components/customButton/CustomButton";
import {
  Anchor,
  Box,
  Button,
  Fieldset,
  TextInput,
  Textarea,
  Image as MantineImage,
} from "@mantine/core";
import Image from "next/image";
import ConnectHome from "../../../assets/svg/connect-home.svg";
import InqueryIcons from "../../../assets/svg/Inquery-icon.svg";
import Overview from "../../../assets/images/overview.png";
import AgentProfile from "../../../assets/svg/userprofile.svg";
import { useAppSelector } from "@/store/hooks";
import { IconPhoneCall } from "@tabler/icons-react";
import { useState } from "react";
import CustomText from "@/components/customText/CustomText";
import CustomModal from "@/components/customModal/CustomModal";
import AuthModal from "../auth/AuthModal";
interface AgentDetailType {
  agentDetail: Array<contactsItemType>;
  form: any;
  enquirySubmitHandler: () => void;
  enquiryLoading: boolean;
  setIsNewModalOpen: (value: string) => void;
  clientLogo: string;
  clientName: string;
  isAgent?: boolean;
}
function AgentInformation({
  agentDetail,
  enquirySubmitHandler,
  form: { getInputProps },
  enquiryLoading,
  setIsNewModalOpen,
  clientLogo,
  clientName,
  isAgent,
}: AgentDetailType) {
  const [isShowContactNumber, setIsShowContactNumber] = useState(false);
  const { token } = useAppSelector((state) => state?.userReducer);
  // const { email, fullName, image, phone } = agentDetail?.[0];

  return (
    <>
      <div className="agent_info_card box-container-card">
        <Box className="agent_info_head">
          <figure>
            <Image src={ConnectHome} alt="ConnectHome" />
          </figure>
          <text>{isAgent ? "Agent Information" : "Landlord Information"}</text>
        </Box>
        <Box className="agent_profile">
          <figure
            style={{
              display: !agentDetail?.[0]?.image ? "flex" : "",
              justifyContent: !agentDetail?.[0]?.image ? "center" : "",
              alignItems: !agentDetail?.[0]?.image ? "center" : "",
            }}
          >
            <MantineImage
              src={agentDetail?.[0]?.image ?? AgentProfile}
              alt="AgentUser"
              style={{
                height: "100%",
                width: "100%",
              }}
              // width={agentDetail?.[0]?.image ? 80 : 60}
              // height={agentDetail?.[0]?.image ? 80 : 60}
            />
          </figure>
          <figcaption>
            <h4>{agentDetail?.[0]?.fullName}</h4>
            <h5>{isAgent ? "Agent of property" : "Landlord of property"}</h5>
          </figcaption>
        </Box>
        {token ? (
          <Box className="agent_profile_detail">
            <h6>Details:</h6>
            {/* <text>{agentDetail?.[0]?.email}</text> */}
            {isShowContactNumber ? (
              <Anchor
                href={`tel:${agentDetail?.[0]?.phone}`}
                target="_blank"
                underline="always"
              >
                {agentDetail?.[0]?.phone}
              </Anchor>
            ) : null}
            {!isShowContactNumber ? (
              <Button
                style={{ marginTop: 10, cursor: "pointer" }}
                variant="outline"
                leftSection={<IconPhoneCall size={18} />}
                onClick={() => {
                  setIsShowContactNumber(true);
                }}
              >
                Show Contact Number
              </Button>
            ) : null}

            {/* <text>3454 maharaj Cir. Syracuse, Connecticut 35624</text> */}
          </Box>
        ) : (
          <Box className="agent_profile_detail">
            <h6>Details:</h6>

            <CustomModal
              key={token}
              actionButton={
                <Button
                  style={{ marginTop: 10, cursor: "pointer" }}
                  variant="outline"
                  leftSection={<IconPhoneCall size={18} />}
                >
                  Show Contact Number
                </Button>
              }
            >
              <AuthModal type="chooseUserType" />
            </CustomModal>
          </Box>
        )}
        <Box className="agent_inquery_form">
          <Box className="agent_info_head">
            <figure>
              <Image src={InqueryIcons} alt="InqueryIcons" />
            </figure>
            <text>Request Enquiry</text>
          </Box>
          <Fieldset>
            <TextInput
              label=""
              placeholder="Full Name"
              {...getInputProps("name")}
            />
            <TextInput
              disabled
              type="number"
              label=""
              placeholder="Phone Number"
              {...getInputProps("phone")}
            />
            <TextInput
              disabled
              label=""
              placeholder="Email Address"
              {...getInputProps("email")}
            />
            <Textarea
              resize="vertical"
              label=""
              placeholder="Message"
              {...getInputProps("message")}
            />
            <CustomButton
              loading={enquiryLoading}
              onClick={() => {
                if (token) {
                  enquirySubmitHandler();
                } else {
                  setIsNewModalOpen("true");
                }
              }}
            >
              Submit
            </CustomButton>
          </Fieldset>
        </Box>
      </div>

      {clientName || clientLogo ? (
        <Box className="agent_info_card agent_overview box-container-card">
          {clientLogo ? (
            <figure>
              <Image src={clientLogo} alt="Overview" height={20} width={20} />
            </figure>
          ) : null}
          {clientName ? <text>{clientName}</text> : null}
        </Box>
      ) : null}
    </>
  );
}

export default AgentInformation;
