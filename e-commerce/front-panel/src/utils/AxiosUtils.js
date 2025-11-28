import api from './api'
import config from '../config'

const baseUrl = config.reactApiUrl;
const token = localStorage.getItem('token');

/**
 * Sends an HTTP request using the specified method, URL, and optional data.
 *
 * @async
 * @function
 * @param {string} method - The HTTP method to use (e.g., 'get', 'post', 'put', 'delete').
 * @param {string} url - The endpoint URL (relative to baseUrl).
 * @param {Object} [data={}] - The request payload for methods other than 'get'.
 * @returns {Promise<any>} The response data from the API.
 * @throws Will throw an error if the request fails.
 */
const request = async (method, url, data = {}, retries = 3) => {
    try {
        const configObj = {
            method,
            url: baseUrl + url,
            headers: { ...(token && { Authorization: `Bearer ${token}` }) },
            data: method !== 'get' ? data : undefined,
        };
        const res = await api(configObj);
        if (res?.status !== 200 && retries > 0) {
            return await request(method, url, data, retries - 1);
        }
        return res?.data;
    } catch (err) {
        console.error(`Error during the ${method.toUpperCase()} request to ${url} : ${err?.status}`);
        if (err.status && err.status === 401) localStorage.clear();
        throw err.response;
    }
};

const get = (url) => request('get', url, {});
const post = (url, data) => request('post', url, data);
const put = (url, data) => request('put', url, data);
const patch = (url, data) => request('patch', url, data);
const destroy = (url) => request('delete', url, {});

// Exporting all methods for easy import in other components
export { get, post, put, patch, destroy }