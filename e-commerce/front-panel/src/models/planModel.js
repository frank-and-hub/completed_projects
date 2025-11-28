import { post } from "../utils/AxiosUtils";

export const initialState = {
    name: '',
    description: '',
    price: '',
    currency: '',
    payment_method: '',
    payment_type: '',
};

export const submitPlan = async (values) => {
    try {
        const res = await post('/plans', values);  // Make API call
        return res;
    } catch (error) {
        throw new Error(error.message);
    }
};

export default function validate(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter name';
    if (!values.description) errors.description = 'Please enter description';
    if (!values.price) errors.price = 'Please enter price';
    if (!values.currency) errors.currency = 'Please enter currency';
    if (!values.payment_method) errors.payment_method = 'Please select payment method';
    if (!values.payment_type) errors.payment_type = 'Please select payment type';
    return errors;
}

export const validateForm = (values) => {
    const errors = validate(values);
    return errors;
};
