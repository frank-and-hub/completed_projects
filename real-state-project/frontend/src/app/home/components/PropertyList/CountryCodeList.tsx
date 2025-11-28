import React, { useEffect, useState } from 'react';
import axios from 'axios';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';
const CountryCodeList = () => {
  const [countries, setCountries] = useState<any>({});

  useEffect(() => {
    axios
      .get('https://restcountries.com/v3.1/all')
      .then((response) => {
        const countryData = response.data.map((country: any) => ({
          name: country.name.common,
          code: country.cca2,
          flag: country.flags.svg, // Using SVG format for better quality
        }));
        setCountries(countryData);
      })
      .catch((error) => {
        console.error('Error fetching country data:', error);
      });
  }, []);

  return (
    <PhoneInput
      country={'us'}
      value={countries.phone}
      onChange={(phone) => setCountries({ phone })}
    />
  );
};

export default CountryCodeList;
