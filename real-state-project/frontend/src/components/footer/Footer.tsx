import React from "react";
import {
  ActionIcon,
  Anchor,
  Center,
  Container,
  Grid,
  Group,
  List,
  Text,
} from "@mantine/core";
import "./footer.scss";
import {
  IconBrandFacebook,
  IconBrandFacebookFilled,
  IconBrandInstagram,
  IconBrandInstagramFilled,
  IconBrandLinkedin,
  IconBrandLinkedinFilled,
  IconBrandYoutube,
  IconBrandYoutubeFilled,
  IconDeviceMobile,
  IconMail,
  IconMapPin,
} from "@tabler/icons-react";
import LogowhSvg from "../../../assets/images/logo-white.svg";
import Image from "next/image";
import { useRouter } from "next/navigation";
import CustomText from "../customText/CustomText";

function Footer() {
  const router = useRouter();
  return (
    <footer>
      <Container size="xl">
        <Grid>
          <Grid.Col span={3.5}>
            <Image
              style={{ cursor: "pointer" }}
              onClick={() => {
                router.push("/");
              }}
              src={LogowhSvg}
              width={200}
              height={200}
              alt="PocketProperty - For Rental Property Listing"
            />
            <CustomText c={"#FFFF"} mt={20} fz={12}>
              Streamline Your Property Journey with South
              <br /> Africa's Premier Property Needs Portal. Discover
              <br />
              Your Ideal Home Faster, with Seamless WhatsApp
              <br /> Integration and Less Hassle.
            </CustomText>
          </Grid.Col>
          {/* <Grid.Col span={1.5}>
            <div className="inner_foot">
              <h4>Quick Links</h4>

              <List>
                <List.Item>
                  <Anchor href="/" underline="never">
                    Home
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor href="/contact-us" underline="never">
                    Contact Us
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/privacy-policy"
                    underline="never"
                    target="_blank"
                  >
                    Pricing
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="https://documenter.getpostman.com/view/36984914/2sAYXBFz2c"
                    underline="never"
                    target="_blank"
                  >
                    API Integration
                  </Anchor>
                </List.Item>
              </List>
            </div>
          </Grid.Col> */}
          <Grid.Col span={1.5}>
            <div className="inner_foot">
              <h4>Rent Property</h4>

              <List>
                <List.Item>
                  <Anchor
                    href="/list-your-property-for-rent/landlords"
                    underline="never"
                  >
                    For Landlords
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/list-your-property-for-rent/agency-owners"
                    underline="never"
                  >
                    For Agency Owner
                  </Anchor>
                </List.Item>
              </List>
            </div>
          </Grid.Col>
          <Grid.Col span={2.5}>
            <div className="inner_foot">
              <h4>Top States for Renting Property</h4>

              <List>
                <List.Item>
                  <Anchor href="/property-for-rent/gauteng" underline="never">
                    Gauteng
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/property-for-rent/kwazulu-natal"
                    underline="never"
                  >
                    KwaZulu-Natal
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/property-for-rent/western-cape"
                    underline="never"
                  >
                    Western Cape
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/property-for-rent/mpumalanga"
                    underline="never"
                  >
                    Mpumalanga
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/property-for-rent/eastern-cape"
                    underline="never"
                  >
                    Eastern Cape
                  </Anchor>
                </List.Item>
              </List>
            </div>
          </Grid.Col>
          <Grid.Col span={1.5}>
            <div className="inner_foot">
              <h4>Others</h4>
              <List>
                <List.Item>
                  <Anchor href="/contact-us" underline="never">
                    Contact Us
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="https://documenter.getpostman.com/view/42115460/2sB2cSiPmD"
                    underline="never"
                    target="_blank"
                  >
                    API Integration
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/terms-conditions"
                    target="_blank"
                    underline="never"
                  >
                    Terms of service
                  </Anchor>
                </List.Item>
                <List.Item>
                  <Anchor
                    href="/privacy-policy"
                    target="_blank"
                    underline="never"
                  >
                    Privacy policy
                  </Anchor>
                </List.Item>
              </List>
            </div>
          </Grid.Col>
          <Grid.Col span={3}>
            <div className="inner_foot">
              <Anchor
                href="https://maps.app.goo.gl/PY1Dju1eQoqPKvLX6"
                target="_blank"
                underline="always"
              >
                <IconMapPin stroke={2} color="#F30051" size={23} />
                Newlands, Cape Town, WC 7700, South Africa
              </Anchor>
              <Anchor
                href="mailto:services@pocketproperty.app"
                target="_blank"
                underline="always"
                pt={7}
              >
                <IconMail stroke={2} color="#F30051" size={20} />{" "}
                services@pocketproperty.app
              </Anchor>
              <Anchor
                // href="phone:+27 79 338 9178"
                href={"whatsapp://send?text=Hello World!&phone=+27 79 338 9178"}
                target="_blank"
                underline="always"
                pt={7}
              >
                <IconDeviceMobile stroke={2} color="#F30051" size={20} />
                +27 79 338 9178
              </Anchor>
            </div>
          </Grid.Col>
        </Grid>
        <Center>
          <div className="social_icon_container">
            <Anchor
              href="https://www.facebook.com/people/PocketProperty/100090450416218/?mibextid=LQQJ4d"
              target="_blank"
              underline="always"
              pt={7}
            >
              <IconBrandFacebookFilled stroke={2} color="#FFF" />
            </Anchor>
          </div>
          <div className="social_icon_container">
            <Anchor
              href="https://www.linkedin.com/company/pocketproperty/?viewAsMember=true"
              target="_blank"
              underline="always"
              pt={7}
            >
              <IconBrandLinkedinFilled stroke={2} color="#FFF" />
            </Anchor>
          </div>
          <div className="social_icon_container">
            <Anchor
              href="https://www.instagram.com/pocketpropertyapp/"
              target="_blank"
              underline="always"
              pt={7}
            >
              <IconBrandInstagramFilled stroke={2} color="#FFF" />
            </Anchor>
          </div>
        </Center>
        <Text className="copy_rights">
          © All rights reserved 2025{" "}
          <Anchor href="/" underline="never">
            PocketProperty
          </Anchor>
        </Text>
      </Container>
    </footer>
  );
}

export default Footer;
