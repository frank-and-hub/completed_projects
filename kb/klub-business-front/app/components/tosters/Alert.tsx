import { Alert } from "@mantine/core";

interface AlertProps {
    icon: string;
    text: string;
    color: string;
    [key: string]: any;
}
export default function AlertToster({ icon, text, color = `blue`, title = "Alert title", ...prop }: AlertProps) {
    return (
        <Alert
            variant="light"
            color={color}
            radius="xl"
            withCloseButton
            title={title}
            icon={icon}
            {...prop}
        >
            {text}
        </Alert >
    );
}