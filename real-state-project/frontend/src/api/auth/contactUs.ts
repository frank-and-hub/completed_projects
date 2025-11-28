import { post_api } from '../root_apis/root_api';

interface contactUsParamsType {
  name: string;
  email: string;
  subject: string;
  message: string;
  re_captcha: string;
}
const contactUs = (data: contactUsParamsType) =>
  post_api('contact-us', { ...data });

export { contactUs };
