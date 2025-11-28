'use client';

import { Modal, Group, Text } from '@mantine/core';
import React from 'react';
import TableButton from '@/components/table/TableButton';
import { radius } from '@/utils/style';

interface ReusableModalProps {
  show: boolean;
  title: string | React.ReactNode;
  body?: string | React.ReactNode;
  handleClose: () => void;
  primaryAction?: () => void;
  secondaryAction?: () => void;
  primaryLabel?: string;
  secondaryLabel?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl' | string;
  primaryVariant: string;
  secondaryVariant: string;
}

const ReusableModal: React.FC<ReusableModalProps> = ({
  show,
  title,
  body,
  handleClose,
  primaryAction,
  secondaryAction,
  primaryLabel,
  secondaryLabel,
  size = 'md',
  primaryVariant,
  secondaryVariant,
}) => {
  return (
    <Modal opened={show} onClose={handleClose} title={title} size={size} centered radius={radius} overlayProps={{ backgroundOpacity: 0.5, blur: 2 }} className={`flex justify-center`} >
      <div className={`text-center`}>
        {typeof body === 'string' ? <Text>{body}</Text> : body}
      </div>
      <Group justify={`center`} mt={`xl`}>
        {secondaryLabel && (
          <TableButton variant={secondaryVariant} onClick={secondaryAction || handleClose} name={secondaryLabel} color={`rgba(0, 0, 0, 0.5)`} />
        )}
        {primaryLabel && (
          <TableButton variant={primaryVariant} onClick={primaryAction} name={primaryLabel} />
        )}
      </Group>
    </Modal>
  );
};

export default ReusableModal;
