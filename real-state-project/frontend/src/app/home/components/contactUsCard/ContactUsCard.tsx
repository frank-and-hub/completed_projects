"use client";
import CustomButton from "@/components/customButton/CustomButton";
import { Group, TextInput, Textarea, Title } from "@mantine/core";
import "./contactUsCard.scss";
import useContactUs from "./useContactUs";
import CustomText from "@/components/customText/CustomText";
import ReCAPTCHA from "react-google-recaptcha";
import { useRef } from "react";
function ContactUsCard() {
  const {
    form: { getInputProps },
    handleSubmit,
    isPending,
    message,
    captchaRef,
  } = useContactUs();

  return (
    <div className="contact_uscard">
      <form>
        <Title>Contact Us</Title>

        <TextInput
          label="Your Name"
          placeholder="Your name"
          mt="lg"
          name="name"
          variant="filled"
          {...getInputProps("name")}
        />

        <TextInput
          label="Email"
          type="email"
          placeholder="Your Email ID"
          mt="lg"
          name="email"
          variant="filled"
          {...getInputProps("email")}
        />

        <TextInput
          label="Subject"
          placeholder="Enter subject"
          mt="lg"
          name="subject"
          variant="filled"
          {...getInputProps("subject")}
        />

        <Textarea
          mt="lg"
          label="Message"
          placeholder="Your message"
          maxRows={10}
          minRows={5}
          autosize
          name="message"
          variant="filled"
          {...getInputProps("message")}
        />

        <Group justify="center" mt="xl">
          <ReCAPTCHA
            sitekey="6Lf74eopAAAAAFuwrK8aumQEHTrtqDxkQcBwlYwi"
            ref={captchaRef}
          />
          <CustomButton
            loading={isPending}
            onClick={() => {
              handleSubmit();
            }}
            size=""
          >
            Submit
          </CustomButton>

          {/* <CustomModal className="requirements_modal_card"
                actionButton={
                    <CustomButton>Submit</CustomButton>
                  }>
                <YourRequirements/>
            </CustomModal> */}
        </Group>
      </form>
      {message ? (
        <CustomText style={{ color: "#F30051", textAlign: "center" }}>
          {message}
        </CustomText>
      ) : null}
    </div>
  );
}

export default ContactUsCard;
