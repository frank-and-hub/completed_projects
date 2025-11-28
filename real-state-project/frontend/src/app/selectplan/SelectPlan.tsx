import React from 'react';
import ModalCloseIcon from '../home/components/modalCloseIcon/ModalCloseIcon';
import { IconChevronLeft } from '@tabler/icons-react';
import { ActionIcon } from '@mantine/core';
import MemberShipCard from '../home/components/memberShipCard/MemberShipCard';
import './seletctplan.scss';

function SelectPlan({ handleClose }: any) {
  return (
    <div className="select_plan_modal">
      <div className="modal_head_close cenetr_back">
        <ActionIcon className="back_btn">
          <IconChevronLeft stroke={2} />
        </ActionIcon>
        <h2>Select Plan</h2>
        <ModalCloseIcon handleClose={handleClose} />
      </div>

      <MemberShipCard
        isFromSearchFilter={true}
        handleClose={handleClose}
        planType="tenant"
      />
    </div>
  );
}

export default SelectPlan;
