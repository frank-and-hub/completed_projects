import ClientReviewCard from "../../landlords/home2/components/clientReviewCard/ClientReviewCard";
import { Box, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import React from "react";
const data = [
  {
    id: 1,
    useName: "Sipho Ndlovu",
    location: "East London, Eastern Cape",
    message:
      "As an agency owner, finding quality tenants for our listed properties has always been a challenge. PocketProperty has completely changed the game. The platform automatically matches tenants with our properties and sends leads via WhatsApp, making it incredibly efficient. The meeting scheduling and contract-building features save us hours of work. Highly recommend it for any real estate agency in South Africa!",
    profileImage:
      "https://s3-alpha-sig.figma.com/img/3636/b4bd/de561d2390a17f411cf88112d5735507?Expires=1740960000&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=MRynGFUg4OUYhxOgiMAGCvXQ4fQLiOQf3Oo8rxG5W8WTlR3eMmFc0SkXp3NEwS4AChNoGX~4byauepLO1LlNsEfT-~mlu8XLIn5hNqrGDuGOyrTf4BhL0XivhH0x8udwbLDnqal960UK9wyLqAVV9PF0f-LKFMnzjcEObNCdgxeOGvl4Xg7cYy-9IcjzYwSntnl6dk6SUaWuzu2r6iEMwflU7feB1D98SL05q3~Y2lXqGf-Sp6EVmySP0fwrOXYnAoK~0XlL9xQWHVdB-V3OVJFfMlmjx0EnmE5Z7GAOuUaX8KUi1Id7SnV6LE0PNuoXWVJ~3NngNH-ds4dmxaQi~w__",
  },
  {
    id: 2,
    useName: "Rajesh Govender",
    location: "Durban, KwaZulu-Natal",
    message:
      "We’ve been using PocketProperty for a few months now, and it’s made property management so much easier. Our listings get matched with tenants instantly, and the WhatsApp notifications ensure we never miss a lead. The ability to book meetings and draft contracts directly in the system has improved our workflow significantly. If you're an agency owner, this platform is a must-have!",
    profileImage:
      "https://s3-alpha-sig.figma.com/img/5de1/f7d4/98a044cbe478b28baec32d37adab3ab6?Expires=1740960000&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=g7xj4fENFbKyE55PcYp1FYY7aSsgDiY9AdtHO1QFaT-0PMl-R7SYXtCDLsdZiSTePbKAZTl4pw6ZNW1SEsi~0hJpZxiK3iyxKQ11ULhbinDQ5PMdbh-yg6Rrjps9T5qcGaSyIi4nbNkqd7BsQ8GSgTEoUjweWIfn5j8pU10SET0g5TvxiZRRQPRA11JloMYxeOwk91zaYVJklevRDihB7z5tmaKHlFjBAMxT-jDFkMD~PWsghm4-6kYOuMaZIXrsa~-pVsOHHwADcfxwaJhLsHaovR~tPgQTBpIJa20KwpVLiI5irmSp69RgSkzMFECTLHx4Y07G3-kCXwUW8l7K6g__",
  },
  {
    id: 3,
    useName: "Jessica van Wyk",
    location: "Cape Town, Western Cape",
    message:
      "Running a real estate agency in South Africa means handling multiple property listings at once. PocketProperty has simplified the way we manage rentals by automatically matching tenants and allowing us to schedule meetings without the hassle. The contract system ensures transparency and speeds up deal closures. This platform has been a great asset to our business!",
    profileImage:
      "https://s3-alpha-sig.figma.com/img/01be/b3b0/4bed7935c8f814a0278810833de021ff?Expires=1740960000&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=Cw4MUfNO7jZM58kbbBu0EvgTHsuOcekmGudIWbiyp72OXLZc2PU4ZyPP4Ec5ALmyiLT~fUOkomf-5MMre7xUKtAqjmg1yhjIgaNw4LVw30K~K2sVDYgPwtpd0MoxpnMjIaPiRQYeOw~zSMzlWnFRrTQKcMaXMa~pUROnsi98bhb878Q7T~1MFM-ABYinkvDggpDe8dlgM6fqNOmz5wd~nizJ7A9P4y-oqo9ZyTlNWVn91wbGuGsUlIU77bYugZrN95~Apx9pmiF0JPAc9SCx9vnBeYe9Wkmp5eYfNKtwHc6wzF6eMaHLOSJQx01GMAyCzoVP1hO-~nEJyV1SPP78qw__",
  },
  {
    id: 4,
    useName: "Danielle Smith",
    location: "Mbombela (Nelspruit), Mpumalanga",
    message:
      "PocketProperty has transformed the way we connect with tenants. Instead of relying on slow responses from other platforms, we now get high-quality leads sent directly via WhatsApp. It’s fast, efficient, and helps us close rental deals much quicker. Any agency looking to streamline their process should try it!",
    profileImage:
      "https://s3-alpha-sig.figma.com/img/6b91/1cb5/3080e74d7962c1c0880f84b7dfb6a98e?Expires=1740960000&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=HeUrVOrXvQ1Goxe5xpxySuKx4uMkNWHh7jK3Fb9-ZsQXnZqo89vLFpz17v-RtJaXCcKPUa0kpvK0c2M3hkLmDvBN~dcoREiN3jYSXLZaTixzeTbvltDHe~LPH81HtEKm~FCO04YYTuyn9AZz0XZ~7hQahJmXOgwABkllq17MkuoxfcLXxKbrXtlublnOrPp9W1KBkhPHCwnKJxf~HZHz3hkoGPLIw~Mk3nG0ysValMJcobKQ9ekYezwPolXsJk~jnc6NTz~xoYZeDXz6oGjLvNqEVjcr3iHQvADnykQ5NV4GlEWAD4fexS1WZy2BcAJ9Il3a~c~rbV6h9sgWt9e~Zw__",
  },
];
function Home3OurClientSection() {
  return (
    <section className="homeCard_sec section_Two">
      <Container size={"lg"}>
        <div className="grid-container">
          <div className="left-side-container ">
            <Flex className="heading_box_flex">
              <Box className="heading_box_sec">
                <p>What Our Clients Say</p>
              </Box>
            </Flex>
            <h2
              className="heading2"
              style={{ textTransform: "uppercase", textAlign: "left" }}
            >
              Agencies That Trust PocketProperty
            </h2>
            <h3 className="heading3" style={{ textAlign: "left" }}>
              Our clients’ success stories reflect our commitment to excellence.
              See how we’ve helped them find their dream homes.
            </h3>
          </div>
          <div className="right-side-container">
            <SimpleGrid cols={{ base: 1, sm: 2, lg: 2 }}>
              {data?.map((item) => (
                <ClientReviewCard item={item} key={item?.id} />
              ))}
            </SimpleGrid>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home3OurClientSection;
