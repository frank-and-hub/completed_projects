import * as yup from "yup";
// const phoneNumberRegex = /^([2-9])(?!\1+$)\d{9}$/;
const phoneNumberRegex = /^\d{8,16}$/;
export const registerValidationSchema = yup.object().shape({
  name: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .matches(/^[a-zA-Z0-9 ]*$/, "Name should not contain special characters.")
    .required("Name is required"),
  country: yup.string().trim().required("Please select country"),
  phone: yup
    .string()
    .trim()
    .matches(phoneNumberRegex, "Please enter valid mobile number")
    .required("Mobile number is Required"),
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
  password: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Password must be at least ${min} characters`)
    .required("Password is required"),
  confirm_password: yup
    .string()
    .trim()
    .oneOf([yup.ref("password"), ""], "Passwords must match")
    .required("Confirm password is required"),
});
export const landlordRegisterValidationSchema = yup.object().shape({
  name: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .matches(/^[a-zA-Z0-9 ]*$/, "Name should not contain special characters.")
    .required("Name is required"),
  country: yup.string().trim().required("Please select country"),

  // image: yup.string().trim().required('Profile Image is required'),
  phone: yup
    .string()
    .trim()
    .matches(phoneNumberRegex, "Please enter valid mobile number")
    .required("Mobile number is Required"),
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
  password: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Password must be at least ${min} characters`)
    .required("Password is required"),
  confirm_password: yup
    .string()
    .trim()
    .oneOf([yup.ref("password"), ""], "Passwords must match")
    .required("Confirm password is required"),
});
export const updateProfileValidationSchema = yup.object().shape({
  name: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .matches(/^[a-zA-Z0-9 ]*$/, "Name should not contain special characters.")
    .required("Name is required"),
  country: yup.string().trim().required("Please select country"),
  // phone: yup
  //   .string()
  //   .trim()
  //   .matches(phoneNumberRegex, 'Please enter valid mobile number')
  //   .required('Mobile number is Required'),
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),

  emplyee_type: yup.string().trim().required("Employee Type is required"),
  live_with: yup
    .string()
    .trim()
    .matches(/^[0-9]+$/, "Live with must be a number")
    .required("Live with is required"),

  // password: yup
  //   .string()
  //   .trim()
  //   .min(6, ({ min }) => `Password must be at least ${min} characters`)
  //   .required('Password is required'),
});
export const otpValidationSchema = yup.object().shape({
  otp: yup
    .string()
    .trim()
    .min(4, ({ min }) => `Password must be at least ${min} characters`)
    .required("Otp is Required"),
});

export const loginValidationSchema = yup.object().shape({
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
  password: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Password must be at least ${min} characters`)
    .required("Password is required"),
});
export const forgotPasswordValidationSchema = yup.object().shape({
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
});
export const setPasswordValidationSchema = yup.object().shape({
  password: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Password must be at least ${min} characters`)
    .required("Password is required"),
  confirm_password: yup
    .string()
    .trim()
    .oneOf([yup.ref("password"), ""], "Passwords must match")
    .required("Confirm password is required"),
});
export const updateProfileOtpValidationSchema = yup.object().shape({
  oldPassword: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Old Password must be at least ${min} characters`)
    .required("Old Password is required"),
  password: yup
    .string()
    .trim()
    .min(6, ({ min }) => `Password must be at least ${min} characters`)
    .required("Password is required"),
  confirm_password: yup
    .string()
    .trim()
    .oneOf([yup.ref("password"), ""], "Passwords must match")
    .required("Confirm password is required"),
});
export const contactUsValidationSchema = yup.object().shape({
  name: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .matches(/^[a-zA-Z0-9 ]*$/, "Name should not contain special characters.")
    .required("Name is required"),
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
  subject: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .required("Subject is required"),
  message: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .required("Message is required"),
});
export const FilterValidationSchema = yup.object().shape({
  province_name: yup.string().trim().required("Province Name is required"),
  city: yup.string().trim().required("City Name is required"),
  suburb_name: yup.string().trim().required("Suburb Name is required"),
  property_type: yup.string().trim().required("Property Type is required"),
  country_name: yup.string().trim().required("Country Name is required"),
  // start_price: yup.string().trim().required('Minimum Price is required'),
  // end_price: yup.string().trim().required('Maximum Price is required'),
  start_price: yup
    .string()
    .typeError("Minimum Price must be a number")
    .test(
      "is-positive-number",
      "Price must be greater than zero",
      function (value = "") {
        return Number(value) >= 0;
      }
    )
    .required("Minimum Price is required"),
  end_price: yup
    .string()
    .typeError("Maximum Price must be a number")
    .test(
      "is-greater",
      "Maximum Price must be greater than Minimum Price",
      function (value = "") {
        const { start_price } = this.parent;
        return Number(value) > Number(start_price);
      }
    )
    .required("Maximum Price is required"),
  no_of_bathroom: yup.string().trim().required("No. of Bathrooms is required"),
  no_of_bedroom: yup.string().trim().required("No. of Bedrooms is required"),
  // currency: yup.string().trim().required('Currency is required'),
});

export const RequestEnquiryValidationSchema = yup.object().shape({
  name: yup
    .string()
    .trim()
    .min(3, ({ min }) => `Minimum length should be ${min}`)
    .matches(/^[a-zA-Z0-9 ]*$/, "Name should not contain special characters.")
    .required("Name is required"),
  phone: yup
    .string()
    .trim()
    .matches(phoneNumberRegex, "Please enter valid mobile number")
    .required("Mobile number is Required"),
  email: yup
    .string()
    .trim()
    .email("Please enter valid email")
    .required("Email address is required"),
  message: yup.string().trim().required(" Message is required"),
});
