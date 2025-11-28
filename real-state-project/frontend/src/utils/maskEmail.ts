const maskEmail = (email: any) => {
  const [localPart, domainPart] = email.split('@');

  if (localPart.length <= 2) {
    // If the local part is too short, just return a masked version directly
    return `****${localPart[localPart.length - 1]}@${domainPart}`;
  }
  if (localPart.length === 3) {
    // If the local part is too short, just return a masked version directly
    return `****${localPart[localPart.length - 2]}${
      localPart[localPart.length - 1]
    }@${domainPart}`;
  }
  if (localPart.length === 4) {
    // If the local part is too short, just return a masked version directly
    return `****${localPart[localPart.length - 3]}${
      localPart[localPart.length - 2]
    }${localPart[localPart.length - 1]}@${domainPart}`;
  }

  // Show first and last character of the local part, mask the rest
  const maskedLocalPart = `****${localPart[localPart.length - 4]}${
    localPart[localPart.length - 3]
  }${localPart[localPart.length - 2]}${localPart[localPart.length - 1]}`;

  return `${maskedLocalPart}@${domainPart}`;
};

export { maskEmail };
