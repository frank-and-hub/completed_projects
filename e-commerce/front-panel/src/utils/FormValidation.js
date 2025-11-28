import { useState } from 'react'

export const useFormValidation = (initialState, validate) => {
    const [formData, setFormData] = useState(initialState);
    const [errors, setErrors] = useState({});

    const handleChange = (e) => {
        const { name, value, type, checked, files } = e.target;
        setFormData({
            ...formData,
            [name]: ((type === 'checkbox') ? checked : ((type === 'file') ? files[0] : value))
        });
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        const validationErrors = validate(formData);
        setErrors(validationErrors);
    }

    return {
        formData,
        errors,
        handleChange,
        handleSubmit,
        setFormData,
    }
}

export const processApiErrors = (apiErrors) => {
    const formattedErrors = {};
    apiErrors.forEach((error) => {
        const { path, msg } = error;
        if (formattedErrors[path]) {
            formattedErrors[path].push(msg);
        } else {
            formattedErrors[path] = [msg];
        }
    });
    return formattedErrors;
};

export const useSignUpFormValidation = (initialState, validate) => {
    const [values, setValues] = useState(initialState);
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setValues({
            ...values,
            [name]: value,
            [name]: type === 'checkbox' ? checked : value,
        });
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        const validationErrors = validate(values);
        setErrors(validationErrors);
        setIsSubmitting(true);
    }

    return {
        values,
        errors,
        handleChange,
        handleSubmit,
        isSubmitting
    }
}

export const useSingInFormValidation = (initialState, validate) => {
    const [values, setValues] = useState(initialState);
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setValues({
            ...values,
            [name]: value
        });
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        const validationErrors = validate(values);
        setErrors(validationErrors);
        setIsSubmitting(true);
    }

    return {
        values,
        errors,
        handleChange,
        handleSubmit,
        isSubmitting
    }
}

export function menuValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter menu name';
    if (!values.route) errors.route = 'Please enter menu route';
    if (!values.icon) errors.icon = 'Please select menu icon';
    return errors;
}

export function bannerValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    // if (!values.icon) errors.icon = 'Please select icon';
    return errors;
}

export function brandValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    if (!values.image) errors.image = 'Please upload image';
    return errors;
}

export function categoryValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    // if (!values.icon) errors.icon = 'Please select icon';
    return errors;
}

export function colorValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.hex_code) errors.hex_code = 'Please select hex code';
    return errors;
}

export function signUpValidation(values) {
    let errors = {}

    // Name validation
    if (!values.name) errors.name = 'Please enter your name';

    // Email validation
    if (!values.email) {
        errors.email = 'Email is required';
    } else if (!/^\S+@\S+\.\S+$/.test(values.email)) {
        errors.email = 'Please enter a valid email address';
    }

    // Password validation
    if (!values.password) {
        errors.password = 'Password is required';
    } else if (values.password.length < 6) {
        errors.password = 'Password needs to be at least 6 characters';
    }

    // Phone validation
    if (!values.phone) {
        errors.phone = 'Phone Number is required';
    } else if (!/^\d{10,}$/.test(values.phone)) {
        errors.phone = 'Phone number must be at least 10 digits and numeric only';
    }

    // Terms and conditions validation
    // if (!values.terms) {
    //     errors.terms = 'You must agree to the terms and conditions';
    // }

    return errors;
}

export function contactValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.title) errors.title = 'Please enter title';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}

export function discountValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.percentage) errors.percentage = 'Please enter percentage';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}

export function productValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    if (!values.specification) errors.specification = 'Please enter specification';
    if (!values.price) errors.price = 'Please enter price';
    if (!values.quantity) errors.quantity = 'Please enter quantity';
    if (!values.discount_id) errors.discount_id = 'Please select discount';
    if (!values.brand_id) errors.brand_id = 'Please select brand';
    if (!values.tags) errors.tags = 'Please select tags';
    if (!values.categories) errors.categories = 'Please select categories';
    if (!values.image) errors.image = 'Please select image';
    return errors;
}

export function chatValidation(values) {
    let errors = {};
    if (!values.receiver_id) errors.receiver_id = 'Please select support agent';
    if (!values.message.trim()) errors.message = 'Please enter your message';
    return errors;
}

export function aboutProfileValidation(values) {
    let errors = {}
    if (!values.title) errors.title = 'Please enter title';
    if (!values.bio) errors.bio = 'Please enter bio';
    if (!values.experience) errors.experience = 'Please enter experience';
    return errors;
}

export function validate(values) {
    let errors = {}
    return errors;
}

export function termsAndConditionsValidation(values) {
    let errors = {}
    if (!values.t_and_c) errors.t_and_c = 'Please enter terms and conditions';
    return errors;
}

export function returnPolicyValidation(values) {
    let errors = {}
    if (!values.info) errors.info = 'Please enter return policy info';
    return errors;
}

export function aboutUsValidation(values) {
    let errors = {}
    if (!values.info) errors.info = 'Please enter about us info';
    return errors;
}

export function roleValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.permissions) errors.permissions = 'Please select permissions';
    return errors;
}

export function serviceValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    // if (!values.icon) errors.icon = 'Please select icon';
    return errors;
}

export function socialDetailValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.url) errors.url = 'Please enter url';
    // if (!values.icon) errors.icon = 'Please select icon';
    return errors;
}

export function otpValidation(values) {
    let errors = {}
    if (!values.otp) {
        errors.otp = 'Please enter your otp';
    } else if (values.otp.length < 6) {
        errors.otp = 'Password needs to be at least 6 characters';
    }
    return errors;
}

export function signInValidation(values) {
    let errors = {}
    if (!values.email) {
        errors.email = 'Enter your Email';
    } else if (!/\S+@\S+\.\S+/.test(values.email)) {
        errors.email = 'Please enter a valid email address';
    }
    if (!values.password) {
        errors.password = 'Please enter your password';
    } else if (values.password.length < 6) {
        errors.password = 'Password needs to be at least 6 characters';
    }
    return errors;
}

export function faqValidation(values) {
    let errors = {}
    if (!values.question) errors.question = 'Please enter question';
    if (!values.answer) errors.answer = 'Please enter answer';
    return errors;
}

export function supportValidation(values) {
    let errors = {}
    if (!values.cell) errors.cell = 'Please enter cell number';
    if (values.cell.length < 8) errors.call = 'cell number should to be at least 8 characters';
    if (!/^\d+$/.test(values.cell)) errors.call = 'Cell number must be a valid number';
    if (!values.type) errors.type = 'Please enter type';
    if (!values.email) errors.email = 'Please enter email';
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(values.email)) errors.email = 'Please enter a valid email address';
    if (!values.address) errors.address = 'Please enter address';
    if (values.address.length < 5 || values.address.length > 200) errors.address = 'Address must be between 5 and 200 characters';
    if (!values.hours_start) errors.hours_start = 'Please enter hours start';
    if (!/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/.test(values.hours_start)) errors.hours_start = 'Start hours must be in valid 24-hour format (HH:mm)';
    if (!values.hours_end) errors.hours_end = 'Please enter hours end';
    if (!/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/.test(values.hours_end)) errors.hours_end = 'End hours must be in valid 24-hour format (HH:mm)';
    if (!values.week_start) errors.week_start = 'Please enter week start';
    // if (!/^Week \d+$/.test(values.week_start)) errors.week_start = 'Week start must be in the format "Week X" (e.g., Week 1)';
    if (!values.week_end) errors.week_end = 'Please enter week end';
    // if (!/^Week \d+$/.test(values.week_end)) errors.week_end = 'Week end must be in the format "Week X" (e.g., Week 1)';
    return errors;
}

export function storeValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.phone) errors.phone = 'Please enter phone';
    if (values.phone.length < 8) errors.phone = 'Phone number should to be at least 8 characters';
    if (!values.email) errors.email = 'Please enter email';
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(values.email)) errors.email = 'Please enter a valid email address';
    if (!values.address) errors.address = 'Please enter address';
    if (values.address.length < 5 || values.address.length > 200) errors.address = 'Address must be between 5 and 200 characters';
    if (!values.city) errors.city = 'Please enter city';
    if (!values.state) errors.state = 'Please enter state';
    if (!values.zipcode) errors.zipcode = 'Please enter zipcode';
    if (!values.country) errors.country = 'Please enter country';
    if (!values.supplier_id) errors.supplier_id = 'Please select supplier';
    return errors;
}

export function subCategoryValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    // if (!values.icon) errors.icon = 'Please select icon';
    if (!values.category) errors.category = 'Please select category';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}

export function userPermissionValidation(values) {
    let errors = {}
    if (!values.user_id) errors.user_id = 'Please select user';
    // if (!values.role_id) errors.role_id = 'Please select role';
    return errors;
}

export function userValidation(values) {
    let errors = {}
    if (!values.first_name) errors.first_name = 'Please enter first name';
    if (values.first_name.length < 3) errors.first_name = 'First name needs to be at least 3 characters';
    if (!values.last_name) errors.last_name = 'Please enter last name';
    if (values.last_name.length < 3) errors.last_name = 'Last name needs to be at least 3 characters';
    if (!values.email) errors.email = 'Please enter email';
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(values.email)) errors.email = 'Please enter a valid email address';
    if (!values.password) errors.password = 'Please enter password';
    if (values.password.length < 8) errors.password = 'Password needs to be at least 8 characters';
    if (!values.phone) errors.phone = 'Please enter phone';
    if (values.phone.length < 8) errors.phone = 'Phone number should to be at least 8 characters';
    if (!values.role_id) errors.role_id = 'Please select role';
    return errors;
}

export function tagValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    return errors;
}

export function unitValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.short_name) errors.short_name = 'Please enter short name';
    return errors;
}

export function wareHouseValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.percentage) errors.percentage = 'Please enter percentage';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}

export function warrantyValidation(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.duration) errors.duration = 'Please select duration';
    if (!values.period) errors.period = 'Please select period';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}

export function departmentValidation (values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.icon) errors.icon = 'Please select icon';
    if (!values.hod_id) errors.hod_id = 'Please select hod';
    return errors;
}

export function pagesValidation(values) {
    let errors = {}
    if (!values.type) errors.type = 'Please enter type';
    if (!values.description) errors.description = 'Please enter description';
    return errors;
}