"use client";

import React, { ReactElement, ReactNode, cloneElement, useEffect } from "react";
import { useDisclosure } from "@mantine/hooks";
import {
  Modal,
  Button,
  UnstyledButton,
  ModalProps,
  ButtonProps,
} from "@mantine/core";
import { useGlobalContext } from "@/utils/context";

interface ICustomModal {
  modalProps?: ModalProps;
  children?: ReactElement;
  actionButton: ReactNode;
  disabled?: boolean;
  className?: string;
  isOpen?: string | boolean;
  onClose?: () => void;
}

function CustomModal({
  modalProps,
  children,
  actionButton,
  disabled,
  className = "comman_modal_custom",
  isOpen,
  onClose,
}: ICustomModal) {
  const [opened, { open, close }] = useDisclosure(false);
  useEffect(() => {
    if (isOpen) {
      open();
    }
  }, [isOpen]);

  return (
    <>
      <Modal
        {...modalProps}
        opened={opened}
        onClose={() => {
          close();
          onClose && onClose();
        }}
        withCloseButton={modalProps?.withCloseButton ?? false}
        className={className}
        closeButtonProps={{}}
        centered={modalProps?.centered ?? true}
        closeOnClickOutside={false}
      >
        {/* Modal content */}
        {children &&
          cloneElement(children, {
            handleClose: () => {
              close();
              onClose && onClose();
            },
          })}
      </Modal>

      <div
        onClick={() => {
          !disabled && open();
        }}
      >
        {actionButton}
      </div>
    </>
  );
}

export default CustomModal;
