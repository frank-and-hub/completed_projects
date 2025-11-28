const planAmountContent: any = {
  tenant: {
    Basic: {
      type: "month",
      title: "Standard listing submission, active for 30 days",
      // bottomList: [
      //   "Instantly receive a curated list of matched properties tailored to your preferences on your preferred schedule on WhatsApp",
      //   "No hassle of submitting rental requests, just explore and discover your perfect home effortlessly",
      //   "5 rental requests",
      // ],
      bottomHeading:
        "Ideal for tenants who want full control over who they contact and when.",
      headingList: [
        // "Craft Your Dream",
        // "See Your Vision",
        // "Activate Your Search",
        // "Get Your Dream Matches",
        "Effortless Property Matching – Receive a curated list of matched properties tailored to your preferences.",
        "Take Control of Your Search – Contact landlords or agents directly when you find a match.",
        "WhatsApp Notifications – Get instant updates on new matches at your preferred schedule.",
        "5 Rental Requests – You decide which properties to reach out to.",
      ],
    },
    Professional: {
      type: "month",
      title: "Standard listing submission, active for 30 days",
      bottomHeading:
        // "Take your property search to the next level with premium benefits",
        "Perfect for busy tenants who want a hassle-free, automated rental search.",
      // bottomList: [
      //   `Send rental requests directly to landlords or agents for matched properties, ensuring you don't miss out on any opportunities`,
      //   "Receive instant WhatsApp notifications for properties where rental requests have been submitted, putting you ahead of the competition for your dream home.",
      //   "5 rental requests",
      // ],
      headingList: [
        // "Craft Your Dream",
        // "See Your Vision",
        // "Activate Your Search",
        // "Get Your Dream Matches",
        "Hands-Free Rental Requests – We automatically submit rental requests on your behalf when a match is found.",
        "Stay Ahead of the Competition – Be the first in line without lifting a finger.",
        "Real-Time WhatsApp Alerts – Get notified instantly when a rental request has been submitted for you.",
        "5 Rental Requests Included – Maximize your chances of securing the perfect home.",
      ],
    },
  },
  privatelandlord: {
    Basic: {
      type: "listing",
      title: "Standard listing submission",

      bottomHeading: `<strong>Plus:</strong> Receive a personalized list of matched tenants, delivered to you on WhatsApp based on your schedule and preferences.`,
      bottomList: [
        // "Instantly receive a curated list of matched properties tailored to your preferences on your preferred schedule on WhatsApp",
        // `<strong>Plus:</strong> Receive a personalized list of matched tenants, delivered to you on WhatsApp based on your schedule and preferences.`,
        // "No hassle of submitting rental requests, just explore and discover your perfect home effortlessly",
        // "5 rental requests",
      ],
      headingList: [
        // "Register and list your property",
        // "Get matched with tenants and receive WhatsApp notifications",
        // "Schedule viewings and events",
        // "Send contracts directly",
        // "Landlord verification through live face capture for secure listings",

        "Create an account and list your property",
        "Get matched with potential tenants",
        "Receive real-time WhatsApp alerts",
        "Schedule viewings and manage events",
        "Secure listing with live face verification for landlords",
        "Share rental contracts directly with tenants",
      ],
    },
    // Professional: {
    //   title: "Standard listing submission",

    //   bottomHeading:
    //   bottomHeading:
    //     "Take your property search to the next level with premium benefits",
    //   bottomList: [
    //     // `Send rental requests directly to landlords or agents for matched properties, ensuring you don't miss out on any opportunities`,
    //     // "Receive instant WhatsApp notifications for propertie where rental requests have been submitted, putting you ahead in the competition for your dream home.",
    //     // "5 rental requests",
    //   ],
    //   headingList: [
    //     "Craft Your Dream",
    //     "See Your Vision",
    //     "Activate Your Search",
    //     "Get Your Dream Matches",
    //   ],
    // },
  },
  agency: {
    Basic: {
      type: "month",

      // title: "Agency account can",
      title: "With an Agency Account, You Can:",
      bottomHeading:
        "<strong>Plus:</strong> Receive a curated list of matched tenants which is  delivered straight to your agent's WhatsApp based on your availability and preferences.",
      bottomList: [
        // "Instantly receive a curated list of matched propertie tailored to your preferences on your preferred schedule on WhatsApp",
        // "No hassle of submitting rental requests, just explore and discover your perfect home effortlessly",
        // "5 rental requests",
      ],
      headingList: [
        // "Create and manage agent profiles",
        // "List and manage multiple properties",
        // "Get agent properties matched with tenants and receive WhatsApp notifications",
        // "Schedule viewings and events",
        // "Send contracts directly",
        // "Control and track agency activities",
        // "Agency verification through providing documents for agencies.",

        "Create and manage multiple agent profiles",
        "List and manage all your rental properties in one place",
        "Get tenant matches for each property, with real-time WhatsApp notifications",
        "Schedule property viewings and important events effortlessly",
        "Send rental contracts directly through the platform",
        "Monitor and control all agency activities from a centralized dashboard",
        "Verify your agency by submitting the required documentation",
      ],
      buttonText: "Get Started",
    },
    // Professional: {
    //   title: "Agency account can",

    //   bottomHeading:
    //     "Take your property search to the next level with premium benefits",
    //   bottomList: [
    //     `Send rental requests directly to landlords or agents for matched properties, ensuring you don't miss out on any opportunities`,
    //     "Receive instant WhatsApp notifications for propertie where rental requests have been submitted, putting you ahead in the competition for your dream home.",
    //     "5 rental requests",
    //   ],
    //   headingList: [
    //     "Craft Your Dream",
    //     "See Your Vision",
    //     "Activate Your Search",
    //     "Get Your Dream Matches",
    //   ],
    // },
  },
};

export default planAmountContent;
