"use client";
import {
  ActionIcon,
  Anchor,
  Box,
  Burger,
  Button,
  Divider,
  Grid,
  Group,
  Switch,
  UnstyledButton,
  rem,
  Avatar,
  Text,
  List,
  HoverCard,
  Center,
  Collapse,
  Flex,
  SimpleGrid,
  Paper,
} from "@mantine/core";
import {
  IconArticle,
  IconBrandFacebook,
  IconBrandInstagram,
  IconBrandLinkedin,
  IconBrandWhatsapp,
  IconBuildingSkyscraper,
  IconCalendarPin,
  IconChartRadar,
  IconChevronDown,
  IconChevronRight,
  IconHome,
  IconLogout,
  IconMapPins,
  IconPhone,
  IconUserCircle,
  IconUsersGroup,
  IconZoomMoney,
} from "@tabler/icons-react";
import Image from "next/image";
import { useEffect, useState } from "react";
import icons1 from "../../../assets/images/menu_icon1.svg";
import icons2 from "../../../assets/images/menu_icon2.svg";
import icons3 from "../../../assets/images/menu_icon3.svg";
import HelloImg from "../../../assets/svg/hello.svg";

import "@/components/header/header.scss";
import CustomModal from "../customModal/CustomModal";
import Profile from "@/app/profile/Profile";
import useNavBar from "./useNavbar";
import AuthModal from "@/app/auth/AuthModal";
import dynamic from "next/dynamic";
import Link from "next/link";
import MessageAlert from "./MessageAlert";
import { data } from "jquery";
import { useRouter } from "next/navigation";
import CustomButton from "../customButton/CustomButton";
import { useDisclosure } from "@mantine/hooks";
import useHeader from "../header/useHeader";
import axios from "axios";
import { closeNotification, notification } from "@/utils/notification";
import useCreditReportGenerate from "@/utils/useCreditReportGenerate";
const CustomText = dynamic(() => import("../customText/CustomText"), {
  ssr: false,
});

interface IHeader {
  toggleMobile: () => void;
  toggleDesktop: () => void;
  mobileOpened: boolean;
  desktopOpened: boolean;
}
function Navbar({
  desktopOpened,
  mobileOpened,
  toggleDesktop,
  toggleMobile,
}: IHeader) {
  const { userDetail, token, alertHandler, data, isPending } = useNavBar();
  const [opened, { toggle }] = useDisclosure(false);
  const [isActive, setIsActive] = useState(Boolean(userDetail?.message_alert));
  const [isMounted, setIsMounted] = useState(false);
  const router = useRouter();
  const { generateCreditReport, isCreditReportAvailable } =
    useCreditReportGenerate();
  useEffect(() => {
    if (data) {
      setIsActive(Boolean(data?.message_alert));
    }
  }, [data]);
  useEffect(() => {
    setIsMounted(true);
  }, []);

  const { onLogout } = useHeader({
    toggleDesktop,
    desktopOpened,
    mobileOpened,
    toggleMobile,
  });

  if (!isMounted) {
    return <></>;
  }

  return (
    <div className="mantine_menu_right">
      <Box
        className="user_profile"
        p={"lg"}
        style={{
          position: "sticky",
          top: 0,
          zIndex: 100,
          backgroundColor: "#fff",
        }}
      >
        <Group>
          <Avatar
            alt="no-profile-image"
            src={userDetail?.image}
            radius="xl"
            size="lg"
            style={{ border: userDetail?.image ? "2px solid #f30051" : 0 }}
            suppressContentEditableWarning={false}
          />

          <div style={{ flex: 1 }}>
            <CustomText c="dimmed" size="md">
              Hello
            </CustomText>
            {/* <Image src={HelloImg} width={20} height={20} alt="" /> */}
            <CustomText className="name_text" size="lg" fw={600}>
              {userDetail?.name}
            </CustomText>

            {/* {token && userDetail?.subscription_expired_date && (
              <div>
                <CustomText
                  fw={"bold"}
                  size="xs"
                  className="expire_date_text"
                  my={"xs"}
                >
                  <span
                    style={{
                      color: "#f30051",
                      fontWeight: "bold",
                      marginRight: "5px",
                    }}
                  >
                    3
                  </span>{" "}
                  Requests Left
                </CustomText>
                <Group className="subscription_expire_date" gap={0}>
                  <CustomText
                    fw={"bold"}
                    size="xs"
                    className="expire_date_text"
                  >
                    Plan Expires On:{" "}
                  </CustomText>
                  <CustomText size="xs">
                    {userDetail?.subscription_expired_date}
                  </CustomText>
                </Group>
              </div>
            )} */}
          </div>
        </Group>
        {token ? (
          <Burger opened={desktopOpened} onClick={toggleDesktop} size="sm" />
        ) : (
          <Burger
            opened={desktopOpened}
            onClick={toggleDesktop}
            hiddenFrom="md"
            size="sm"
          />
        )}

        <Divider className="profile_border" />
      </Box>

      {token && userDetail?.subscription_expired_date ? (
        <SimpleGrid spacing="xs" cols={{ base: 1, sm: 1, md: 2 }} mx={"sm"}>
          <Paper withBorder p={"xs"} radius={"sm"} shadow="xs" bg="#f30051">
            <CustomText size="xs" className="expire_date_text" c={"#FFF"}>
              Requests Left
            </CustomText>
            <CustomText
              fw={"bold"}
              size="sm"
              className="expire_date_text"
              c={"#FFF"}
            >
              {userDetail?.pending_request_count}
            </CustomText>
          </Paper>

          <Paper withBorder p={"xs"} radius={"sm"} shadow="xs" bg={"#000"}>
            <CustomText size="xs" className="expire_date_text" c={"#fff"}>
              Plan Expires On:
            </CustomText>
            <CustomText
              fw={"bold"}
              size="sm"
              className="expire_date_text"
              c={"#fff"}
            >
              {userDetail?.subscription_expired_date}
            </CustomText>
          </Paper>
        </SimpleGrid>
      ) : null}

      {token ? (
        <ul className="links_port_list">
          <li>
            <CustomModal
              disabled={token ? false : true}
              actionButton={
                <Button
                  variant="transparent"
                  leftSection={<IconUserCircle stroke={1.5} />}
                >
                  Profile
                </Button>
              }
            >
              <AuthModal type="profile" />
            </CustomModal>
          </li>
          <li>
            <Button
              onClick={() => {
                router.replace("/requests");
              }}
              variant="transparent"
              leftSection={<Image src={icons1} width={18} height={18} alt="" />}
            >
              Property Requests
            </Button>
          </li>
          <li>
            <Button
              onClick={generateCreditReport}
              variant="transparent"
              leftSection={<IconChartRadar size={20} />}
            >
              {isCreditReportAvailable
                ? "View Credit Report"
                : "Get Credit Report"}
            </Button>
          </li>

          {/* <li>
            <Button
              onClick={() => {
                router.replace("/portals");
              }}
              variant="transparent"
              leftSection={<Image src={icons1} width={18} height={18} alt="" />}
              component="a"
            >
              Properties
            </Button>
          </li>
          <li>
            <Button
              disabled={token ? false : true}
              variant="transparent"
              leftSection={<Image src={icons2} width={18} height={18} alt="" />}
              component="a"
              onClick={() => {
                router.replace("/property-needs");
              }}
            >
              {token ? (
                <Link style={{ padding: 0 }} href={"/property-needs"}>
                  All Property Needs
                </Link>
              ) : (
                "All Property Needs"
              )}
            </Button>
          </li>*/}

          <li>
            <Button
              variant="transparent"
              leftSection={<IconCalendarPin />}
              onClick={() => {
                router.replace("/calendar-events");
              }}
            >
              <Link style={{ padding: 0 }} href={"/calendar-events"}>
                {/* Calendar Events */}
                Property Viewing Schedule
              </Link>
            </Button>
          </li>
          <li>
            <Button
              variant="transparent"
              leftSection={<Image src={icons3} width={18} height={18} alt="" />}
              onClick={() => {
                router.replace("transaction-history");
              }}
            >
              <Link style={{ padding: 0 }} href={"/transaction-history"}>
                Transaction History
              </Link>
            </Button>
          </li>
          <li className="mantine-hidden-from-md">
            <Button
              hiddenFrom="md"
              variant="transparent"
              leftSection={<IconArticle size={18} />}
              onClick={() => {
                router.replace("blog");
              }}
            >
              <Link style={{ padding: 0 }} href={"/blog"}>
                Blog
              </Link>
            </Button>
          </li>
          <li>
            <div className="alerts_whatsapp">
              <span>
                {" "}
                <IconBrandWhatsapp stroke={1.5} /> Alerts
              </span>{" "}
              <Switch
                size="xl"
                onLabel="YES"
                offLabel="NO"
                checked={isActive}
                onChange={(event) => {
                  alertHandler(event?.currentTarget?.checked);
                  setIsActive(event?.currentTarget?.checked);
                }}
                className={`header_switch`}
                classNames={{
                  track: isActive
                    ? "active_track_switch"
                    : "inactive_track_swtich",
                  thumb: isActive
                    ? "active_thumb_switch"
                    : "inactive_thumb_swtich",
                }}
              />
            </div>
          </li>
          {userDetail?.subscription_type === "Basic" ? (
            <li>
              <CustomModal
                actionButton={
                  <Button
                    variant="transparent"
                    leftSection={
                      <Image src={icons3} width={18} height={18} alt="" />
                    }
                  >
                    Alert Schedule
                  </Button>
                }
              >
                <MessageAlert />
              </CustomModal>
            </li>
          ) : null}
          <li style={{ flex: 1 }}>
            <Button
              onClick={onLogout}
              variant="transparent"
              color="181A20"
              leftSection={<IconLogout stroke={1.5} color="#f30051" />}
            >
              Logout
            </Button>
          </li>

          <HelpAndSupport />
        </ul>
      ) : (
        <>
          <ul className="links_port_list">
            <li>
              <Button
                variant="transparent"
                leftSection={<IconHome stroke={2} />}
                // component="a"
                onClick={() => {
                  router.replace("/");
                  toggleDesktop();
                }}
              >
                <Link style={{ padding: 0 }} href={"/"}>
                  PocketProperty - Home
                </Link>
              </Button>
            </li>
            <li>
              <Button
                variant="transparent"
                leftSection={<IconBuildingSkyscraper stroke={2} />}
                // component="a"
                onClick={() => {
                  router.replace("/#plans");
                  toggleDesktop();
                }}
              >
                <Link style={{ padding: 0 }} href={"#plans"}>
                  PocketProperty - Pricing
                </Link>
              </Button>
            </li>
            <li>
              <Button
                variant="transparent"
                leftSection={<IconZoomMoney stroke={2} />}
                rightSection={<IconChevronDown size={14} stroke={1.5} />}
                // component="a"
                onClick={toggle}
              >
                <Link style={{ padding: 0 }} href={""}>
                  Rent Property
                </Link>
              </Button>
            </li>
            <Collapse in={opened}>
              <ul>
                <li>
                  <Button
                    variant="transparent"
                    // leftSection={<IconMapPins stroke={2} />}
                    // component="a"
                    onClick={() => {
                      router.replace("/list-your-property-for-rent/landlords");
                      toggleDesktop();
                    }}
                  >
                    <Link
                      style={{ padding: 0, marginLeft: 30 }}
                      href={"/list-your-property-for-rent/landlords"}
                    >
                      Landlords
                    </Link>
                  </Button>
                </li>
                <li>
                  <Button
                    variant="transparent"
                    // leftSection={<IconUsersGroup stroke={2} />}
                    onClick={() => {
                      router.replace(
                        "/list-your-property-for-rent/agency-owners"
                      );
                      toggleDesktop();
                    }}
                  >
                    <Link
                      style={{ padding: 0, marginLeft: 30 }}
                      href={"/list-your-property-for-rent/agency-owners"}
                    >
                      Agency Owner
                    </Link>
                  </Button>
                </li>
              </ul>
            </Collapse>
            <li>
              <Button
                variant="transparent"
                leftSection={<IconArticle size={18} />}
                onClick={() => {
                  router.replace("blog");
                }}
              >
                <Link style={{ padding: 0 }} href={"/blog"}>
                  Blog
                </Link>
              </Button>
            </li>
            <li>
              <Button
                variant="transparent"
                leftSection={<IconPhone stroke={2} />}
                onClick={() => {
                  router.replace("/contact-us");
                  toggleDesktop();
                }}
              >
                <Link style={{ padding: 0 }} href={"/contact-us"}>
                  Contact Us
                </Link>
              </Button>
            </li>

            {token ? null : (
              <div style={{ flex: 1 }}>
                <Group>
                  <CustomModal
                    key={token}
                    actionButton={
                      <CustomButton ml={20} mb={20}>
                        <Text>Login</Text>
                      </CustomButton>
                    }
                  >
                    <AuthModal type="chooseUserType" />
                  </CustomModal>
                  <CustomModal
                    disabled={!!token}
                    actionButton={
                      <CustomButton
                        bg={"#000"}
                        iconProps={{ color: "#000" }}
                        ml={20}
                        mb={20}
                      >
                        <Text>Sign Up Now</Text>{" "}
                      </CustomButton>
                    }
                  >
                    <AuthModal type="signup" />
                  </CustomModal>
                </Group>
              </div>
            )}
            <HelpAndSupport />
          </ul>
        </>
      )}
    </div>
  );
}

export default Navbar;

const HelpAndSupport = () => {
  return (
    <div className="menu_inner_btm">
      <Grid>
        <Grid.Col span={12}>
          <span>Need Support ?</span>
          <Anchor
            href="mailto:services@pocketproperty.app"
            target="_blank"
            underline="always"
          >
            services@pocketproperty.app
          </Anchor>
        </Grid.Col>
        <Grid.Col span={12}>
          <span>Customer Care Number</span>
          <Anchor href="tel:+27 79 338 9178" target="_blank" underline="always">
            +27 79 338 9178
          </Anchor>
        </Grid.Col>
      </Grid>
      <div className="menu_inner_side">
        <h4>Follow us</h4>
        <Group gap={0} className="actionIcon_lists">
          <Anchor
            href="https://www.facebook.com/people/PocketProperty/100090450416218/?mibextid=LQQJ4d
"
            target="_blank"
          >
            <ActionIcon size="xl" color="#fff" variant="subtle">
              <IconBrandFacebook stroke={1.5} />
            </ActionIcon>
          </Anchor>
          <Anchor
            href="https://www.instagram.com/pocketpropertyapp/"
            target="_blank"
          >
            <ActionIcon size="xl" color="#fff" variant="subtle">
              <IconBrandInstagram stroke={1.5} />
            </ActionIcon>
          </Anchor>
          <Anchor
            href="https://www.linkedin.com/company/pocketproperty/?viewAsMember=true"
            target="_blank"
          >
            <ActionIcon size="xl" color="#fff" variant="subtle">
              <IconBrandLinkedin stroke={1.5} />
            </ActionIcon>
          </Anchor>
        </Group>
      </div>
    </div>
  );
};
