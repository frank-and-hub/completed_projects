"use client";
import AuthModal from "@/app/auth/AuthModal";
import { useAppSelector } from "@/store/hooks";
import {
  Avatar,
  Burger,
  Button,
  Center,
  Container,
  Group,
  Menu,
  Text,
  Title,
  Tooltip,
} from "@mantine/core";
import {
  IconChevronDown,
  IconCirclePlus,
  IconCirclePlusFilled,
  IconList,
  IconLogout,
  IconPlus,
  IconUserCircle,
} from "@tabler/icons-react";
import dynamic from "next/dynamic";
import Image from "next/image";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import LogoSvg from "../../../assets/images/logo.svg";
import "./header.scss";
import useHeader from "./useHeader";
import { link } from "fs";
import CustomText from "../customText/CustomText";
import AdvanceFilter from "@/app/home/components/advanceFilter/AdvanceFilter";
const CustomModal = dynamic(() => import("../customModal/CustomModal"), {
  ssr: false,
});
const CustomButton = dynamic(() => import("../customButton/CustomButton"), {
  ssr: false,
});
const links = [
  { link: "/", label: "Home" },
  { link: "/#plans", label: "Pricing" },
  {
    link: "/",
    label: "Rent Property",
    links: [
      {
        link: "/list-your-property-for-rent/landlords",
        label: "For Landlords",
      },
      {
        link: "/list-your-property-for-rent/agency-owners",
        label: "For Agency Owner",
      },
    ],
  },
  { link: "/contact-us", label: "Contact Us" },
  { link: "/blog", label: "Blog" },
];
interface IHeader {
  toggleMobile: () => void;
  toggleDesktop: () => void;
  mobileOpened: boolean;
  desktopOpened: boolean;
}
function Header({
  toggleDesktop,
  toggleMobile,
  desktopOpened,
  mobileOpened,
}: IHeader) {
  const pathname = usePathname();
  const { token, userDetail } = useAppSelector((state) => state.userReducer);

  // const { onLogout } = useHeader({
  //   toggleDesktop,
  //   desktopOpened,
  //   mobileOpened,
  //   toggleMobile,
  // });

  const [active, setActive] = useState(links[0].link);
  const items = links.map((link) => {
    const menuItems = link.links?.map((item, index) => (
      <Link
        key={index}
        prefetch={true}
        href={item.link}
        className="link"
        scroll={true}
        data-active={active === item.link || undefined}
        onClick={(event) => {
          setActive(item.link);
        }}
        style={{ background: "transparent" }}
      >
        <Menu.Item
          key={item.link}
          style={{
            backgroundColor: active === item.link ? "#f30051" : "",
            color: active === item.link ? "#FFF" : "#000",
          }}
        >
          <CustomText size="xs">{item.label}</CustomText>
        </Menu.Item>
      </Link>
    ));
    if (menuItems) {
      return (
        <Menu
          key={link.label}
          trigger="hover"
          transitionProps={{ exitDuration: 0 }}
          withinPortal
          withArrow
          arrowSize={14}
          shadow="xl"
        >
          <Menu.Target>
            <Link
              href={link.link}
              className="link"
              onClick={(event) => {
                event.preventDefault();
              }}
            >
              <Center>
                <span className="link">{link.label}</span>
                <IconChevronDown size={14} stroke={1.5} />
              </Center>
            </Link>
          </Menu.Target>
          <Menu.Dropdown>{menuItems}</Menu.Dropdown>
        </Menu>
      );
    }

    return (
      <Link
        key={link.label}
        href={link.link}
        className="link"
        scroll={true}
        data-active={active === link.link || undefined}
        onClick={(event) => {
          setActive(link.link);
        }}
      >
        {link.label}
      </Link>
    );
  });

  const [isMounted, setIsMounted] = useState(false);
  useEffect(() => {
    setIsMounted(true);
  }, []);

  const router = useRouter();
  if (!isMounted) {
    return <></>;
  }
  return (
    <header className="header">
      <Container size="xl" className="inner">
        <figure className="mobile_logo">
          <Image
            style={{ cursor: "pointer" }}
            onClick={() => {
              router.push("/");
            }}
            src={LogoSvg}
            width={200}
            height={200}
            alt="PocketProperty - Find Properties to Rent"
          />
        </figure>
        <Group gap={5} visibleFrom="md">
          {items}
          {token && pathname.includes("requests") ? (
            <CustomModal
              className="comman_modal_custom_next"
              actionButton={
                <Tooltip withArrow label="Add Request" position="left">
                  <CustomButton className="add_request_btn" px={"sm"}>
                    <Title
                      ms={"0.1rem"}
                      order={5}
                      size="xs"
                      className="mantine-visible-from-sm"
                    >
                      Create Request
                    </Title>
                  </CustomButton>
                </Tooltip>
              }
            >
              <AdvanceFilter />
            </CustomModal>
          ) : null}
        </Group>

        <Group hiddenFrom="sm" className="navbar_mobile_menu">
          <Menu shadow="md" width={200}>
            <Menu.Target>
              <Button>
                <IconList stroke={1.75} />
              </Button>
            </Menu.Target>
            <Menu.Dropdown className="navbar_mobile_menu_dropdown">
              {items}
            </Menu.Dropdown>
          </Menu>
        </Group>

        <div className="right_toggle_sec">
          {/* {token && (
            <Button
              hiddenFrom="md"
              onClick={() => {
                onLogout();
              }}
              variant="transparent"
              color="181A20"
              leftSection={<IconLogout stroke={1.5} />}
            >
              Logout
            </Button>
          )} */}

          {token && pathname.includes("requests") ? (
            <CustomModal
              className="comman_modal_custom_next"
              actionButton={
                <>
                  <Button className="add_request_btn_mobile  mantine-hidden-from-sm">
                    <IconPlus stroke={1.5} />
                  </Button>
                  <CustomButton
                    hiddenFrom="md"
                    className="add_request_btn mantine-visible-from-sm"
                    px={"sm"}
                  >
                    <Title
                      ms={"0.1rem"}
                      order={5}
                      size="xs"
                      className="mantine-visible-from-sm"
                    >
                      Create Request
                    </Title>
                  </CustomButton>
                </>
              }
            >
              <AdvanceFilter />
            </CustomModal>
          ) : null}

          <div className="right_toggle_sec_auth">
            <Group>
              {pathname === "/list-your-property-for-rent/landlords" &&
              !token ? (
                <Link
                  href={window.location.origin + "/privatelandlord/login"}
                  target="_blank"
                >
                  <CustomButton>Login</CustomButton>
                </Link>
              ) : pathname === "/list-your-property-for-rent/agency-owners" &&
                !token ? (
                <Link
                  href={window.location.origin + "/agency/login"}
                  target="_blank"
                >
                  <CustomButton>Login</CustomButton>
                </Link>
              ) : token ? null : ( // </Button> //   Logout // > //   leftSection={<IconLogout stroke={1.5} />} //   color="181A20" //   variant="transparent" //   }} //     onLogout(); //   onClick={() => { // <Button
                <CustomModal
                  key={token}
                  actionButton={<CustomButton>Login</CustomButton>}
                >
                  <AuthModal type="chooseUserType" />
                </CustomModal>
              )}

              {token ? null : pathname ===
                "/list-your-property-for-rent/agency-owners" ? (
                <Link
                  href={"https://form.jotform.com/242595895839581"}
                  target="_blank"
                >
                  <CustomButton
                    bg={"#000"}
                    pl={10}
                    iconProps={{ color: "#000" }}
                    fz={12}
                  >
                    Sign Up Now
                  </CustomButton>
                </Link>
              ) : (
                <CustomModal
                  disabled={
                    !!token &&
                    pathname !== "/list-your-property-for-rent/landlords"
                  }
                  actionButton={
                    userDetail &&
                    pathname !== "/list-your-property-for-rent/landlords" ? (
                      <Button
                        variant="transparent"
                        color="181A20"
                        leftSection={
                          userDetail?.image ? (
                            <Avatar
                              src={userDetail?.image}
                              radius="xl"
                              size="md"
                              suppressContentEditableWarning={false}
                            />
                          ) : (
                            <IconUserCircle stroke={1.5} />
                          )
                        }
                      >
                        {userDetail?.name}
                      </Button>
                    ) : (
                      <CustomButton
                        bg={"#000"}
                        pl={10}
                        iconProps={{ color: "#000" }}
                        fz={12}
                      >
                        Sign Up Now
                      </CustomButton>
                    )
                  }
                >
                  <AuthModal
                    type={
                      pathname === "/list-your-property-for-rent/landlords"
                        ? "landlordSignUp"
                        : "signup"
                    }
                  />
                </CustomModal>
              )}
            </Group>
          </div>

          {/* {token ? ( */}
          <Group className="nav_toggle_btn" h="100%" px="md">
            {token ? (
              <Burger
                opened={desktopOpened}
                onClick={toggleDesktop}
                // hiddenFrom="md"
                size="sm"
              />
            ) : (
              <Burger
                opened={desktopOpened}
                onClick={toggleDesktop}
                hiddenFrom="md"
                size="sm"
              />
            )}
          </Group>
          {/* ) : null} */}
        </div>
      </Container>
    </header>
  );
}

export default Header;
