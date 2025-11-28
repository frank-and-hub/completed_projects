export const StaticOptions = (type: string) => {
  const optionsMap: Record<string, { value: string; label: string }[]> = {
    gender: [
      { value: 'MALE', label: 'Male' },
      { value: 'FEMALE', label: 'Female' },
      { value: 'OTHER', label: 'Other' },
      { value: 'PREFER_NOT_TO_SAY', label: 'Prefer Not To Say' },
    ],
    relationshipStatus: [
      { value: 'SINGLE', label: 'Single' },
      { value: 'MARRIED', label: 'Married' },
      { value: 'DIVORCED', label: 'Divorced' },
      { value: 'ENGAGED', label: 'Engaged' },
      { value: 'IN_A_RELATIONSHIP', label: 'In a Relationship' },
      { value: 'OPEN_RELATIONSHIP', label: 'Open Relationship' },
      { value: 'ITS_COMPLICATED', label: "It's Complicated" },
      { value: 'FOR_FUN', label: 'For Fun' },
    ],
    deviceType: [
      { value: 'ANDROID', label: 'Android' },
      { value: 'IOS', label: 'IOS' },
      { value: 'WEB', label: 'Web' },
      { value: 'OTHER', label: 'Other' },
    ],
    planType: [
      { value: 'FREE', label: 'Free' },
      { value: 'BASIC', label: 'Basic' },
      { value: 'PREMIUM', label: 'Premium' },
      { value: 'ENTERPRISE', label: 'Enterprise' },
    ],
    durationType: [
      { value: 'DAY', label: 'Day' },
      { value: 'WEEK', label: 'Week' },
      { value: 'MONTH', label: 'Month' },
      { value: 'YEAR', label: 'Year' },
    ],
    directionType: [
      { value: 'asc', label: 'Asc' },
      { value: 'desc', label: 'Desc' },
    ],
    reportStatus: [
      { value: 'PENDING', label: 'Pending' },
      { value: 'RESOLVED', label: 'Resolved' },
      { value: 'REJECTED', label: 'Rejected' },
    ],
    friendshipStatus: [
      { value: 'PENDING', label: 'Pending' },
      { value: 'ACCEPTED', label: 'Accepted' },
      { value: 'REJECTED', label: 'Rejected' },
      { value: 'BLOCKED', label: 'Blocked' },
    ],
    paymentMethod: [
      { value: 'CREDIT_CARD', label: 'CreditCard' },
      { value: 'PAY_PAL', label: 'PayPal' },
      { value: 'BANK_TRANSFER', label: 'BankTransfer' },
      { value: 'CRYPTO', label: 'Crypto' },
      { value: 'CASH', label: 'Cash' },
      { value: 'OTHER', label: 'Other' },
    ],
    paymentStatus: [
      { value: 'PENDING', label: 'Pending' },
      { value: 'COMPLETED', label: 'Completed' },
      { value: 'FAILED', label: 'Failed' },
      { value: 'REFUNDED', label: 'Refunded' },
      { value: 'CANCELLED', label: 'Cancelled' },
    ],
    notificationType: [
      { value: 'CHAT', label: 'Chat' },
      { value: 'EVENT', label: 'Event' },
      { value: 'PROMOTION', label: 'Promotion' },
      { value: 'INFO', label: 'Info' },
      { value: 'WARNING', label: 'Warning' },
      { value: 'ALERT', label: 'Alert' },
    ],
    messageType: [
      { value: 'TEXT', label: 'Text' },
      { value: 'IMAGE', label: 'Image' },
      { value: 'VIDEO', label: 'Video' },
      { value: 'AUDIO', label: 'Audio' },
      { value: 'FILE', label: 'File' },
    ],
    fileType: [
      { value: 'IMAGE', label: 'Image' },
      { value: 'VIDEO', label: 'Video' },
      { value: 'AUDIO', label: 'Audio' },
      { value: 'DOCUMENT', label: 'Document' },
      { value: 'PDF', label: 'PDF' },
      { value: 'OTHER', label: 'Other' },
    ],
    status: [
      { value: 'ACTIVE', label: 'Active' },
      { value: 'INACTIVE', label: 'Inactive' },
    ],
  };

  return optionsMap[type] || [];
};