"use client";
import React, { ReactNode } from "react";
import "./FrequentlyAskedQuestions.scss";
import { Accordion } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
interface FAQAccordionProps {
  data: { value: string; description: ReactNode }[];
}
function FrequentlyAskedQuestions({ data }: FAQAccordionProps) {
  const items = data.map((item) => (
    <Accordion.Item key={item.value} value={item.value}>
      <Accordion.Control>
        <h3 style={{ textAlign: "left" }}>{item.value}</h3>
      </Accordion.Control>
      <Accordion.Panel>
        <h4 style={{ textAlign: "left" }}>{item.description}</h4>
      </Accordion.Panel>
    </Accordion.Item>
  ));
  return (
    <Accordion
      defaultValue="Apples"
      classNames={{ chevron: "chevron" }}
      chevron={
        <div style={{ border: "1px solid #C7C7C7", borderRadius: "50%" }}>
          <IconPlus className="icon" />
        </div>
      }
    >
      {items}
    </Accordion>
  );
}

export default FrequentlyAskedQuestions;
