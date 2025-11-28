'use client';

import { createTheme, virtualColor } from '@mantine/core';

export const theme = createTheme({
  /* Put your mantine theme override here */
  colors: {
    'voilet': ['#F30051', '#F30051', '#F30051', '#F30051', '#F30051', '#F30051', '#F30051', '#F30051', '#F30051', '#F30051'],
  },
  primaryColor: 'voilet',
  fontFamily:''

  // colors: {
  //   primary: virtualColor({
  //     name: 'primary',
  //     dark: 'pink',
  //     light: 'cyan',
  //   }),
  // },
});
