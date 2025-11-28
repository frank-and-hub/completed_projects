import AuthModal from "@/app/auth/AuthModal";
import CustomButton from "@/components/customButton/CustomButton";
import CustomModal from "@/components/customModal/CustomModal";
import { Card, Grid, List, Title } from "@mantine/core";
import {
  IconBuildingEstate,
  IconBuildingSkyscraper,
} from "@tabler/icons-react";
import "./memberShipCard.scss";
import useMemberShipCard from "./useMemberShipCard";
import { planAmount } from "@/api/plans/plan";
import planAmountContent from "./planAmountContent";
import Link from "next/link";
function MemberShipCard({
  isFromSearchFilter,
  handleClose,
  planType,
}: {
  isFromSearchFilter: boolean;
  handleClose?: () => void;
  planType: "tenant" | "privatelandlord" | "agency";
}) {
  const {
    planAmountData,
    subscribe,
    isNewModalOpen,
    setIsNewModalOpen,
    id,
    check_subscriptionLoading,
  } = useMemberShipCard({
    isFromSearchFilter,
    handleClose,
  });

  const renderData = (
    data: { amount: string; id: string; plan_name: string; type: string }[]
  ) => {
    return (
      <Grid>
        {data?.map((item) => {
          return (
            <Grid.Col span={6} key={item?.id}>
              <Card shadow="sm" padding="lg" radius="md" withBorder>
                <figure>
                  <IconBuildingEstate size={"3.5rem"} stroke={1.0} />
                </figure>
                <div className="plans_range">
                  <h5>{item?.plan_name}</h5>
                  {item?.amount === "0" || !item?.amount ? (
                    <h2>Free</h2>
                  ) : (
                    <h2>
                      {item?.amount}
                      <span>Rand</span>
                    </h2>
                  )}
                  <h6>
                    per{" "}
                    {planAmountContent?.[item?.type]?.[item?.plan_name]?.type}
                  </h6>
                </div>

                <div className="plans_points">
                  {/* <h4>Standard listing submission, active for 30 days</h4> */}
                  <h4>
                    {planAmountContent?.[item?.type]?.[item?.plan_name]?.title}
                  </h4>
                  <List>
                    {planAmountContent?.[item?.type]?.[
                      item?.plan_name
                    ]?.headingList?.map((item: any, index: number) => {
                      return <List.Item key={index}>{item}</List.Item>;
                    })}
                    {/* <List.Item>Craft Your Dream</List.Item>
                    <List.Item>See Your Vision</List.Item>
                    <List.Item>Activate Your Search</List.Item>
                    <List.Item>Get Your Dream Matches</List.Item> */}
                  </List>
                </div>

                <div className="next_plans_points">
                  {planAmountContent?.[item?.type]?.[item?.plan_name]
                    ?.bottomHeading && (
                    <Title
                      order={4}
                      dangerouslySetInnerHTML={{
                        __html:
                          planAmountContent?.[item?.type]?.[item?.plan_name]
                            ?.bottomHeading,
                      }}
                    >
                      {
                        // planAmountContent?.[item?.type]?.[item?.plan_name]
                        //   ?.bottomHeading
                      }
                    </Title>
                  )}
                  <ul>
                    {planAmountContent?.[item?.type]?.[
                      item?.plan_name
                    ]?.bottomList?.map((item: any, index: number) => {
                      return <li key={index}>{item}</li>;
                    })}
                    {/* <li>
                      Instantly receive a curated list of matched properties
                      tailored to your preferences on your preferred schedule on
                      WhatsApp.
                    </li>
                    <li>
                      No hassle of submitting rental requests, just explore and
                      discover your perfect home effortlessly
                    </li>
                    <li>5 rental requests</li> */}
                  </ul>
                </div>

                <Link
                  onClick={(e) => {
                    if (!(planType === "agency")) {
                      e.preventDefault();
                    }
                  }}
                  href={"https://form.jotform.com/242595895839581"}
                  target={"_blank"}
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                  }}
                >
                  <CustomButton
                    loaderProps={{ color: "#F30051" }}
                    loading={check_subscriptionLoading && id === item?.id}
                    onClick={() => {
                      {
                        planType === "agency"
                          ? null
                          : planType === "privatelandlord"
                          ? setIsNewModalOpen("true")
                          : subscribe(item?.id, item?.amount);
                      }
                    }}
                  >
                    {planAmountContent?.[item?.type]?.[item?.plan_name]
                      ?.buttonText ?? "Join Now"}
                  </CustomButton>
                </Link>
              </Card>
            </Grid.Col>
          );
        })}
      </Grid>
    );
  };

  return (
    <section className="membership_sec_in">
      {renderData(planAmountData[planType])}
      <CustomModal
        actionButton={null}
        isOpen={isNewModalOpen}
        onClose={() => {
          setIsNewModalOpen("");
        }}
      >
        <AuthModal
          type={planType === "privatelandlord" ? "landlordSignUp" : "login"}
        />
      </CustomModal>
    </section>
  );
}

export default MemberShipCard;
