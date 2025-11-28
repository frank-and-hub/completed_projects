import { get_api, get_api_data, post_api } from '../root_apis/root_api';

const planAmount = () => get_api_data('plans-amount');
const checkSubscription = () => post_api('check-subscription');
export { planAmount, checkSubscription };
