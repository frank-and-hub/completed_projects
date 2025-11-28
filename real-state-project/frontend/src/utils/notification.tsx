import { DefaultMantineColor } from "@mantine/core";
import { notifications } from "@mantine/notifications";
import { IconCheck, IconX } from "@tabler/icons-react";
import { ReactNode } from "react";

interface INotification {
  title?: string;
  message: string;
  onClose?: () => void;
  onOpen?: () => void;
  type?: "error" | "success" | "loading";
  color?: DefaultMantineColor;
  icon?: ReactNode;
  autoClose?: boolean | number;
  id?: string;
}

const notification = ({
  message,
  title,
  onClose,
  onOpen,
  type = "success",
  color,
  icon,
  autoClose = 5000,
  id,
}: INotification) =>
  type === "success"
    ? notifications.show({
        // id: "success-notification",
        id: id,
        withCloseButton: true,
        onClose,
        onOpen,
        autoClose,
        title: title ?? "Success",
        message,
        icon: icon ?? <IconCheck size="1rem" />,
        className: "my-notification-class",
        color: color ?? "teal",
      })
    : type === "error"
    ? notifications.show({
        id: id ?? "error-notification",
        withCloseButton: true,
        onClose,
        onOpen,
        autoClose,
        title: title ?? "Error",
        message,
        color: color ?? "red",
        icon: icon ?? <IconX />,
        className: "my-notification-class",
      })
    : notifications.show({
        id: id ?? "loading-notification",
        withCloseButton: true,
        onClose,
        onOpen,
        title: title ?? "Loading...",
        message,
        color,
        icon: icon ?? <IconX />,
        className: "my-notification-class",
        loading: true,
      });

const updateNotification = ({
  message,
  title,
  onClose,
  onOpen,
  type,
  icon,
  color,
  autoClose = 5000,
}: INotification) =>
  notifications.update({
    id:
      type === "success"
        ? "success-notification"
        : type === "error"
        ? "error-notification"
        : "loading-notification",
    color,
    title,
    message,
    onClose,
    onOpen,
    icon: icon ?? <IconCheck size="1rem" />,
    autoClose,
  });

const closeNotification = () => {
  notifications.clean();
};
export { notification, updateNotification, closeNotification };
