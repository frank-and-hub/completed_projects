import __DEV__ from "./devCheck";

const createIconUrl = (name: string) => {
  const baseUrl = getBaseURl() + "/assets/icon/";
  //   const baseUrl = 'https://pocketproperty.app/assets/icon/';

  const url = `${baseUrl}${name}.png`;
  return url;
};

export default createIconUrl;

export const getBaseURl = () => {
  if (typeof window !== "undefined") {
    return __DEV__
      ? // ? "https://staging.pocketproperty.app"
        "https://staging.pocketproperty.app"
      : window
      ? window.location.origin
      : "";
  } else {
    return __DEV__
      ? "https://staging.pocketproperty.app"
      : //  ?  "https://pocketproperty.app"
        "https://pocketproperty.app";
  }
};

export const getCreditReportDomain = () => {
  return __DEV__ ? "pocket-property-staging" : "pocket-property";
};
