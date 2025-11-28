'use client';
import { capitalizeFirstLetter } from '@/utils/capitalizeFiesrtLetter';
import { List } from '@mantine/core';
import dayjs from 'dayjs';
import React from 'react';

const dataSerHead = [
  {
    heading: 'Province:',
    subHeading: 'Petersburg Borough',
  },
  {
    heading: 'Suburb:',
    subHeading: 'Anchorage',
  },
  {
    heading: 'Move-in date:',
    subHeading: '25-6-2024',
  },
];
interface propertyServiceHeadingType {
  province_name: string;
  Suburb: string;
  MoveInDate: any;
  cityName: string;
}
function PropertyServiceHeading({
  MoveInDate,
  Suburb,
  province_name,
  cityName,
}: propertyServiceHeadingType) {
  return (
    <div>
      <List className="PropertyService_head_list">
        <List.Item>
          <span>Province</span>
          <strong>{capitalizeFirstLetter(province_name)}</strong>
        </List.Item>
        <List.Item>
          <span>City</span>
          <strong>{capitalizeFirstLetter(cityName)}</strong>
        </List.Item>
        <List.Item>
          <span>Suburb</span>
          <strong>{capitalizeFirstLetter(Suburb)}</strong>
        </List.Item>
        <List.Item>
          <span>Move-in date:</span>
          <strong>
            {MoveInDate ? dayjs(MoveInDate).format('DD/MM/YYYY') : '-'}
          </strong>
        </List.Item>
      </List>
    </div>
  );
}

export default PropertyServiceHeading;
