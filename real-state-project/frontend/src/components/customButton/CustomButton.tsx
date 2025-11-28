"use client";
import { Box, BoxProps, Button, ButtonProps } from "@mantine/core";
import { IconArrowNarrowUp, IconProps } from "@tabler/icons-react";
import React from "react";
interface ICutomButton extends ButtonProps {
  onClick?: React.MouseEventHandler<HTMLButtonElement>;
  iconProps?: IconProps;
  iconContainerBoxProps?: BoxProps;
}
function CustomButton({
  children,
  iconProps,
  iconContainerBoxProps,
  onClick,
  ...props
}: ICutomButton) {
  return (
    <Button onClick={onClick} className={`btn ${props?.className}`} {...props}>
      {children}{" "}
      <Box className="icon-container" {...iconContainerBoxProps}>
        <IconArrowNarrowUp {...iconProps} stroke={1.5} />
      </Box>
    </Button>
  );
}

export default CustomButton;
