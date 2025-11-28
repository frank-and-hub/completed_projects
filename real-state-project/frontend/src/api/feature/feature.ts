import { get_api, get_api_data, post_api } from '../root_apis/root_api';

const featureList = () => get_api_data('features-list');

export { featureList };
