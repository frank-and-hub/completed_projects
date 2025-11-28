import { Box, Container, Flex, SimpleGrid, Text } from "@mantine/core";
import FrequentlyAskedQuestions from "./components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
import CustomButton from "@/components/customButton/CustomButton";
import JsonLdScript from "../JsonLdScript";
import Link from "next/link";

const faqData = [
  {
    value: "Is PocketProperty free for tenants?",
    description:
      "No, PocketProperty offers an affordable Basic & Professional Plan, which allows you to effortlessly explore apartments, houses, and studio apartments for rent. With this plan, you receive 5 rental requests per month and instant WhatsApp notifications for matched properties. For up-to-date pricing details, please visit our pricing page.",
  },
  {
    value: "How do I get WhatsApp notifications for new rental listings?",
    description:
      "Simply subscribe to the Basic Plan, set your preferences, and receive real-time alerts for private property to rent, apartments for rent, and houses for rent that match your search criteria.",
  },
  {
    value: "Can I contact landlords directly?",
    description:
      "Yes! PocketProperty ensures direct communication with landlords and agents—no middlemen, no hidden charges. Once you receive a property match on WhatsApp to rent, you have to apply on that property. Thereafter you can see the contact details of Landlord and then you can directly call them to book meeting further. Also, you can submit your enquiry for further assistance.",
  },
  {
    value: "What types of properties can I find on PocketProperty?",
    description:
      "PocketProperty offers a wide range of rental options, including studio apartments, private property to rent, apartments for rent, and houses for rent. Whether you're looking for a budget-friendly rental or a luxury apartment, you’ll find a match suited to your needs.",
  },
  {
    value: "How does PocketProperty ensure the listings are verified?",
    description:
      "We carefully screen and verify each property to rent to protect tenants from rental scams. Our platform only lists properties from trusted landlords and agents, ensuring a safe and reliable renting experience.",
  },
];
function FrequentlyAskedQuestionsSection() {
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    // name: "Featured Rental Properties",
    // itemListElement: properties.map((property, index) => ({
    mainEntity: [
      {
        "@type": "Question",
        name: "Is PocketProperty free for tenants?",
        acceptedAnswer: {
          "@type": "Answer",
          text: "No, PocketProperty offers an affordable Basic & Professional Plan, which allows you to effortlessly explore apartments, houses, and studio apartments for rent. With this plan, you receive 5 rental requests per month and instant WhatsApp notifications for matched properties. For up-to-date pricing details, please visit our pricing page.",
        },
      },
      {
        "@type": "Question",
        name: "How do I get WhatsApp notifications for new rental listings?",
        acceptedAnswer: {
          "@type": "Answer",
          text: "Simply subscribe to the Basic Plan, set your preferences, and receive real-time alerts for private property to rent, apartments for rent, and houses for rent that match your search criteria.",
        },
      },
      {
        "@type": "Question",
        name: "Can I contact landlords directly?",
        acceptedAnswer: {
          "@type": "Answer",
          text: "Yes! PocketProperty ensures direct communication with landlords and agents—no middlemen, no hidden charges. Once you receive a property match on WhatsApp to rent, you have to apply on that property. Thereafter you can see the contact details of Landlord and then you can directly call them to book meeting further. Also, you can submit your enquiry for further assistance.",
        },
      },
      {
        "@type": "Question",
        name: "What types of properties can I find on PocketProperty?",
        acceptedAnswer: {
          "@type": "Answer",
          text: "PocketProperty offers a wide range of rental options, including studio apartments, private property to rent, apartments for rent, and houses for rent. Whether you're looking for a budget-friendly rental or a luxury apartment, you will find a match suited to your needs.",
        },
      },
      {
        "@type": "Question",
        name: "How does PocketProperty ensure the listings are verified?",
        acceptedAnswer: {
          "@type": "Answer",
          text: "Simply subscribe to the Basic Plan, set your preferences, and receive real-time alerts for private property to rent, apartments for rent, and houses for rent that match your search criteria.",
        },
      },
    ],
  };

  return (
    <>
      <JsonLdScript schemaData={itemListSchema} />
      <section className="homeCard_sec section_Two">
        <Container size={"lg"}>
          <div className="grid-container">
            <div className="left-side-container ">
              <Flex className="heading_box_flex">
                <Box className="heading_box_sec">
                  <p>Help Center</p>
                </Box>
              </Flex>
              <h2
                className="heading2"
                style={{ textTransform: "uppercase", textAlign: "left" }}
              >
                Frequently asked questions
              </h2>
            </div>

            <div className="right-side-container">
              <FrequentlyAskedQuestions data={faqData} />
              <Flex
                justify={"space-between"}
                mt={20}
                pl={10}
                align={"center"}
                className="still_have_qus_container"
              >
                <Flex className="still_have_qus">
                  <span
                    style={{
                      fontSize: "18px ",
                      fontWeight: 600,
                      color: "#f30051",
                    }}
                  >
                    Still have questions?{" "}
                  </span>
                  <p
                    style={{
                      fontSize: "18px ",
                      fontWeight: 600,
                      color: "#2C2C2C",
                      marginLeft: "3px",
                    }}
                  >
                    Chat with us on WhatsApp!
                  </p>
                </Flex>
                <Link
                  // href={" https://api.whatsapp.com/send?phone=+27 79 338 9178"}
                  // href={
                  //   "whatsapp://send?text=Hello World!&phone=+27 79 338 9178"
                  // }
                  href={"https://api.whatsapp.com/send?phone=+27 79 338 9178"}
                  target={"_blank"}
                >
                  <CustomButton>Chat Now</CustomButton>
                </Link>
              </Flex>
            </div>
          </div>
          {/* <Grid>
          <Grid.Col span={4}>
            <Flex>
              <Box className="heading_box_sec">
                <p>Help Center</p>
              </Box>
            </Flex>
            <h2 style={{ textTransform: "uppercase", textAlign: "left" }}>
              Frequently asked questions
            </h2>
          </Grid.Col>
          <Grid.Col span={8}>
            <FrequentlyAskedQuestions />
            <Flex
              justify={"space-between"}
              mt={20}
              pl={10}
              align={"center"}
              className="still_have_qus_container"
            >
              <Flex className="still_have_qus">
                <span
                  style={{
                    fontSize: "18px ",
                    fontWeight: 600,
                    color: "#f30051",
                  }}
                >
                  Still have questions?{" "}
                </span>
                <p
                  style={{
                    fontSize: "18px ",
                    fontWeight: 600,
                    color: "#2C2C2C",
                    marginLeft: "3px",
                  }}
                >
                  Chat with us on WhatsApp!
                </p>
              </Flex>
              <CustomButton>
                <Text fz={15}>Chat Now</Text>
              </CustomButton>
            </Flex>
          </Grid.Col>
        </Grid> */}
        </Container>
      </section>
    </>
  );
}

export default FrequentlyAskedQuestionsSection;
