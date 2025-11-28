"use client";
import CustomModal from "@/components/customModal/CustomModal";
import Footer from "@/components/footer/Footer";
import Header from "@/components/header/Header";
import Navbar from "@/components/navbar/Navbar";
import { useGlobalContext } from "@/utils/context";
import { AppShell, ScrollArea } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import NewAuthModal from "../auth/NewAuthModal";

import "../globals.scss";
import AdvanceFilter from "../home/components/advanceFilter/AdvanceFilter";
import LogOut from "../logOut/LogOut";
import PaymentMethod from "../paymentMethod/PaymentMethod";
import SelectPlan from "../selectplan/SelectPlan";
import ThankYou from "../thankyou/ThankYou";
import MessageAlert from "@/components/navbar/MessageAlert";
import AuthModal from "../auth/AuthModal";
import useDisableIosTextFieldZoom from "./useDisableIosTextFieldZoom";
import "@mantine/carousel/styles.css";
import Script from "next/script";
import {
  updatePropertyInformation,
  updatePropertySearch,
  updateUserInformation,
} from "@/store/reducer/userReducer";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { useEffect } from "react";
import { APIProvider } from "@vis.gl/react-google-maps";
function MantineContainer({ children }: any) {
  useDisableIosTextFieldZoom();
  const dispatch = useAppDispatch();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const router = useRouter();
  const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
  const [desktopOpened, { toggle: toggleDesktop }] = useDisclosure(false);
  const { isModalOpen, setIsModalOpen, setContextValue } = useGlobalContext();
  const { token } = useAppSelector((state) => state.userReducer);
  const removeParam = (paramName: any) => {
    const newParams = new URLSearchParams(searchParams!);
    newParams.delete(paramName);
    router.replace(`${pathname}?${newParams.toString()}`);
  };

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
    }));
    window.dispatchEvent(new Event("new-event"));
  };
  useEffect(() => {
    if (!token) {
      dispatch(updateUserInformation(undefined));

      const target = pathname.split("/");

      if (
        [
          "property-needs",
          "transaction-history",
          "portals",
          "calendar-events",
          "requests",
        ].includes(target?.[1])
      ) {
        router.replace("/");
      }
    }

    return () => {};
  }, [token]);

  return (
    <>
      {/* <Script
        strategy="afterInteractive"
        src="https://www.googletagmanager.com/gtag/js?id=G-GGM6C5J8R1"
      />
      <Script id="google-analytics" strategy="afterInteractive">
        {`
          window.dataLayer = window.dataLayer || [];
          function gtag(){window.dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-GGM6C5J8R1');
        `}
      </Script> */}
      <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY!}>
        <AppShell
          navbar={{
            width: 300,
            breakpoint: "sm",
            collapsed: {
              mobile: !mobileOpened,
              desktop: !desktopOpened,
            },
          }}
          classNames={{
            navbar:
              mobileOpened || desktopOpened
                ? "active-custom-nav-bar"
                : "custom-nav-bar",
            main:
              mobileOpened || desktopOpened
                ? "active-custom-main"
                : "custom-main",
          }}
        >
          <AppShell.Header>
            {/* {pathname === '/terms-conditions' ||
        pathname === '/privacy-policy' ? null : (
          <Header
            toggleMobile={toggleMobile}
            toggleDesktop={toggleDesktop}
            desktopOpened={desktopOpened}
            mobileOpened={mobileOpened}
          />
        )} */}
            <Header
              toggleMobile={toggleMobile}
              toggleDesktop={toggleDesktop}
              desktopOpened={desktopOpened}
              mobileOpened={mobileOpened}
            />
          </AppShell.Header>
          <AppShell.Navbar style={{ overflowY: "auto" }}>
            {/* <AppShell.Section grow my="md" component={ScrollArea}> */}
            <Navbar
              toggleMobile={toggleMobile}
              toggleDesktop={toggleDesktop}
              desktopOpened={desktopOpened}
              mobileOpened={mobileOpened}
            />
            {/* </AppShell.Section> */}
          </AppShell.Navbar>
          <AppShell.Main>
            {children}

            {/* {pathname === '/terms-conditions' ||
        pathname === '/privacy-policy' ? null : (
          <Footer />
        )} */}
            <Footer />
          </AppShell.Main>
          <div>
            {isModalOpen === "login" ? (
              <CustomModal
                actionButton={null}
                isOpen={isModalOpen}
                onClose={() => {
                  setIsModalOpen("");
                  resetContextValues();
                  dispatch(updatePropertyInformation(undefined));
                  dispatch(updatePropertySearch(false));
                }}
              >
                {isModalOpen === "login" ? (
                  <NewAuthModal type="login" />
                ) : (
                  <div></div>
                )}
              </CustomModal>
            ) : isModalOpen === "advanceFilter" ? (
              <CustomModal
                className={"comman_modal_custom_next"}
                actionButton={null}
                isOpen={isModalOpen}
                onClose={() => {
                  setIsModalOpen("");
                  // resetContextValues();
                }}
              >
                <AdvanceFilter isFromSearch={true} />
              </CustomModal>
            ) : isModalOpen === "planThank" ? (
              <CustomModal
                actionButton={null}
                className="thankyou_modal_sr"
                isOpen={isModalOpen}
                onClose={() => {
                  resetContextValues();
                  setIsModalOpen("");
                  removeParam("payment");
                }}
              >
                <ThankYou title="Your payment has been successfully completed." />
              </CustomModal>
            ) : isModalOpen === "messageAlert" ? (
              <CustomModal actionButton={null} isOpen={isModalOpen}>
                <MessageAlert isClose={false} />
              </CustomModal>
            ) : isModalOpen === "authModal" ? (
              <CustomModal actionButton={null} isOpen={isModalOpen}>
                <AuthModal type="profile" isClose={false} />
              </CustomModal>
            ) : (
              <CustomModal
                className={
                  isModalOpen === "selectPlan"
                    ? "select_plan_modal"
                    : isModalOpen === "logout"
                    ? "logout_modal_card"
                    : isModalOpen === "thankYou"
                    ? "thankyou_modal_sr"
                    : isModalOpen === "payment"
                    ? "paymentmethod_modal_card"
                    : ""
                }
                actionButton={null}
                isOpen={isModalOpen}
                onClose={() => {
                  resetContextValues();
                  setIsModalOpen("");
                  // dispatch(updatePropertyInformation(undefined));
                  // dispatch(updatePropertySearch(false));
                }}
              >
                {isModalOpen === "selectPlan" ? (
                  <SelectPlan />
                ) : isModalOpen === "logout" ? (
                  <LogOut />
                ) : isModalOpen === "thankYou" ? (
                  <ThankYou
                    title="Your request has been successfully submitted."
                    description="You will receive alerts about matching properties via your registered WhatsApp mobile number."
                  />
                ) : isModalOpen === "payment" ? (
                  <PaymentMethod />
                ) : (
                  <div></div>
                )}
              </CustomModal>
            )}
          </div>
        </AppShell>
      </APIProvider>
    </>
  );
}

export default MantineContainer;
