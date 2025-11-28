import * as yup from 'yup';

export const validEmailProvidersRegex = /^[\w.+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|yopmail\.com)$/i;
export const validEmailRegex = /^\S+@\S+$/;
export const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;

export const validateEmail = (value: string): string | null => {
  if (!value) return 'Email is required';
  if (!validEmailRegex.test(value)) {
    return 'Invalid email';
  }
  if (!validEmailProvidersRegex.test(value)) {
    return 'Only Gmail, Yahoo, Outlook/Hotmail are allowed';
  }
  return null;
};

export const validatePassword = (value: string): string | null => {
  if (!value) return 'Password is required';

  if (value.length < 8 || value.length > 16) {
    return 'Password must be between 8 and 16 characters';
  }
  
  if (!/[a-z]/.test(value)) {
    return 'Password must include at least one lowercase letter';
  }

  if (!/[A-Z]/.test(value)) {
    return 'Password must include at least one uppercase letter';
  }

  if (!/\d/.test(value)) {
    return 'Password must include at least one number';
  }

  if (!/[\W_]/.test(value)) {
    return 'Password must include at least one special character';
  }
  if (!strongPasswordRegex.test(value)) {
    return 'Password must be 8–16 characters, with upper/lowercase, number, and special character';
  }
  return null;
};

export const validateConfirmPassword = (password: string) => (value: string): string | null => {
  return value === password ? null : 'Passwords do not match';
};

export const validateAgreeTerm = (value: boolean): string | null => {
  return value ? null : "You must agree to the terms and conditions.";
};

export const validateUserRistration = yup.object({
  firstName: yup.string().required('First Name is required'),
  lastName: yup.string(),
  email: yup.string().email('Invalid email').required('Email is required'),
  phone: yup
    .string()
    .required('Phone number is required')
    .min(10, 'Phone number must be at least 10 digits'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required(),
  confirmPassword: yup
    .string()
    .oneOf([yup.ref('password')], 'Passwords must match')
    .required('Confirm Password is required'),
});
