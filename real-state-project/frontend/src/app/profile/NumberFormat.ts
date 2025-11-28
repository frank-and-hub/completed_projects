const phoneNumberAutoFormat = (number: string): string => {
  // const number = phoneNumber.trim().replace(/[^0-9]/g, '');

  if (number.length < 3) return number;
  if (number.length < 5) return number.replace(/(\d{2})(\d{1})/, '$1-$2');
  if (number.length === 5) return number.replace(/(\d{2})(\d{3})/, '$1-$2');
  if (number.length === 6)
    return number.replace(/(\d{2})(\d{3})(\d{1})/, '$1-$2-$3');
  if (number.length === 7)
    return number.replace(/(\d{2})(\d{3})(\d{2})/, '$1-$2-$3');
  if (number.length === 8)
    return number.replace(/(\d{2})(\d{3})(\d{2})(\d{1})/, '$1-$2-$3-$4');
  if (number.length === 9)
    return number.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1-$2-$3-$4');
  if (number.length === 10)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{1})/,
      '$1-$2-$3-$4-$5'
    );
  if (number.length === 11)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})/,
      '$1-$2-$3-$4-$5'
    );
  if (number.length === 12)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})(\d{1})/,
      '$1-$2-$3-$4-$5-$6'
    );
  if (number.length === 13)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})(\d{1})/,
      '$1-$2-$3-$4-$5-$6'
    );
  if (number.length === 14)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})(\d{3})/,
      '$1-$2-$3-$4-$5-$6'
    );
  if (number.length === 15)
    return number.replace(
      /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})(\d{3})(\d{1})/,
      '$1-$2-$3-$4-$5-$6-$7'
    );
  //   if (number.length === 16)
  return number.replace(
    /(\d{2})(\d{3})(\d{2})(\d{2})(\d{2})(\d{3})(\d{2})/,
    '$1-$2-$3-$4-$5-$6-$7'
  );
};
export default phoneNumberAutoFormat;
