function capitalizeFirstLetter(str: string) {
  if (!str) return str; // Handle empty strings or null values
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function addSpaceInString(str: string) {
  const t = str.replace(/([a-z])([A-Z])/g, '$1 $2');
  return t;
}

function formatTimeWithSeconds(timeString: string) {
  if(timeString){
    const [hours = '00', minutes = '00', seconds = '00'] = timeString.split(':');
    return [hours.padStart(2, '0'), minutes.padStart(2, '0')].join(':');
  }
}

export { addSpaceInString, capitalizeFirstLetter, formatTimeWithSeconds };
