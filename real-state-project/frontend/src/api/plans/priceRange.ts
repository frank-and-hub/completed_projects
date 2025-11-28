import { get_api_data } from '../root_apis/root_api';

const priceRange = () => get_api_data('price-range');

export { priceRange };
