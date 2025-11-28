import CustomModal from "@/components/customModal/CustomModal";
import { ActionIcon, Button, Container } from "@mantine/core";
import { IconSearch } from "@tabler/icons-react";
import Image from "next/image";
import advancedicon from "../../../assets/images/advanced_icon.svg";
import AdvanceFilter from "./components/advanceFilter/AdvanceFilter";
import InputSearch from "./components/inputSearch/InputSearch";
import "./sections.scss";

function SectionOne() {
  // useEffect(() => {}, [
  //   document.addEventListener('event', () => {
  //     console.log('hello');
  //   }),
  // ]);
  // const { onHandleSearch, placeInputRef } = useSectionOne();
  // const icon = <IconInfoCircle />;

  return (
    <section className="header_pocket" id="home">
      <Container size={"xl"}>
        <div className="header_form_wd">
          <h1 style={{ textDecoration: "prep" }}>
            Matchmaker for Rentals: {"\n"}Your Dream{" "}
            <span> {"\n"} Property Awaits!</span>
          </h1>
          <h2>
            {/* Streamline Your Property Search with South Africa’s Premier Rental
            Platform. Find Your Ideal Home Faster – Houses, Apartments & Private
            Properties for Rent with Seamless WhatsApp Integration and Less
            Hassle! */}
            Searching for your perfect home takes time. We make it simple — tell
            us what you need, and we’ll send matching rentals to your WhatsApp.
            No apps. No endless scrolling. Just homes that fit.
          </h2>
          <form className="header_form_card">
            <div className="header_form">
              {/* <Input
                placeholder="Enter an address, place, city"
                // ref={placeInputRef}
                leftSection={
                  <IconBuildingSkyscraper color="black" stroke={1.5} />
                }
              /> */}

              <InputSearch />
              {/* <TextInput
                onChange={(value) => {
                  onHandleSearch(value.target?.value);
                }}
                leftSection={
                  <IconBuildingSkyscraper color="black" stroke={1.5} />
                }
                placeholder="Enter an address, place, city"
              /> */}
              <CustomModal
                className="comman_modal_custom_next"
                actionButton={
                  <Button
                    h={50}
                    visibleFrom="md"
                    ms={"xs"}
                    // onClick={() => {
                    //   // placeInputRef.current.value = null;
                    // }}
                    className="advance_btn"
                    // variant="transparent"
                    // color="#181A20"
                    radius={"md"}
                  >
                    {/* <Image
                      src={advancedicon}
                      width={22}
                      height={22}
                      alt="Property to rent in South Africa"
                    />{" "} */}
                    <IconSearch
                      stroke={2}
                      style={{
                        marginRight: "5px",
                      }}
                    />
                    Start My Rental Match
                  </Button>
                }
              >
                <AdvanceFilter isFromSearch={true} />
              </CustomModal>
              <ActionIcon
                ms={"sm"}
                hiddenFrom="md"
                className="search_btn"
                size="xxl"
                radius="50%"
                w={55}
                h={55}
                style={{ minWidth: "55px" }}
                aria-label="Custom xxl size"
              >
                <CustomModal
                  className="comman_modal_custom_next"
                  actionButton={<IconSearch stroke={2} />}
                >
                  <AdvanceFilter isFromSearch={true} />
                  {/* <PaymentMethod /> */}
                </CustomModal>
              </ActionIcon>
            </div>
          </form>
        </div>
      </Container>
    </section>
  );
}

export default SectionOne;
