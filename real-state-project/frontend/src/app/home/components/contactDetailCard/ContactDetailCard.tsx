import React from "react";
import "./contactDetailCard.scss";
import { Anchor, List } from "@mantine/core";
import {
  IconClock,
  IconMail,
  IconMapPin,
  IconPhoneCall,
} from "@tabler/icons-react";

interface SectionFiveProps {
  title: string;
  description: string;
}

function ContactDetailCard() {
  return (
    <div className="get_contact_info">
      <div className="title_card_sc">
        <h2>Get in Touch!</h2>
        <p>
          Connect with us effortlessly and let's kickstart your journey towards
          your dream home! Whether you have questions, feedback, or simply want
          to explore our services further, we're here to assist you every step
          of the way.
        </p>
      </div>
      <List>
        <List.Item>
          <article>
            <IconMapPin size={"32px"} stroke={1.5} />
            <h4>Address</h4>
            <p>Newlands Cape Town, WC 7700 South Africa</p>
          </article>
        </List.Item>
        <List.Item>
          <article>
            <IconPhoneCall size={"32px"} stroke={1.5} />
            <h4>Call Us</h4>
            <Anchor
              href="tel:+27 79 338 9178"
              target="_blank"
              underline="always"
            >
              +27 79 338 9178
            </Anchor>
          </article>
        </List.Item>
        <List.Item>
          <article>
            <IconMail size={"32px"} stroke={1.5} />
            <h4>Email Us</h4>
            <Anchor
              href="mailto:services@pocketproperty.app"
              target="_blank"
              underline="always"
            >
              services@pocketproperty.app
            </Anchor>
          </article>
        </List.Item>
        <List.Item>
          <article>
            <IconClock size={"32px"} stroke={1.5} />
            <h4>Working Hours</h4>
            <p>Mon - Fri: 9AM to 5PM</p>
          </article>
        </List.Item>
      </List>
    </div>
  );
}

export default ContactDetailCard;
