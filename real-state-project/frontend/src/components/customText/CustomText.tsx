import { Text, TextProps } from "@mantine/core";
import React from "react";
interface ICustomText extends TextProps {
  children?: any;
}
function CustomText({ children, ...props }: ICustomText) {
  return <Text {...props}>{children}</Text>;
}

export default CustomText;
