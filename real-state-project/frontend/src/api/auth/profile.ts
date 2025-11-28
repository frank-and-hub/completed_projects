import { get_api, post_api } from "../root_apis/root_api";

const getProfile = (): Promise<ProfileDetailType> => get_api("profile");

const updateProfile = ({ data }: profileUpdateParamsType) =>
  post_api("profile-update", data, {
    headers: {
      "Content-Type": "multipart/form-data",
    },
  });

interface profileVerify {
  // otp: string;
  // name: string;
  // image: any;
  // phone: string;
  // verifytype: string;
}
const profileVerify = ({ data }: { data: FormData }) =>
  post_api("profile-verify", data, {
    headers: {
      "Content-Type": "multipart/form-data",
    },
  });
interface profileUpdatePasswordParamsType {
  old_password: string;
  new_password: string;
  confirm_password: string;
}

const profileUpdatePassword = (data: profileUpdatePasswordParamsType) =>
  post_api("change-password", { ...data });

const getThankyouDetails = (id: string): Promise<invitationAcceptType> =>
  get_api(`invite/thank_you/${id}`);

export {
  getProfile,
  updateProfile,
  profileVerify,
  profileUpdatePassword,
  getThankyouDetails,
};
