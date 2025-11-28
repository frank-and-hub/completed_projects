import axios from 'axios'
import config from '../config'
const baseUrl = config.reactApiUrl;

const api = axios.create({
  baseURL: baseUrl,
  headers: {
    'Content-Type': 'application/json'
  }
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token') ?? null;
  if (token) config.headers['Authorization'] = `Bearer ${token}`; // console.info('👍'); 
  return config;
}, (error) => Promise.reject(error) // console.info('👎');
);

// api.interceptors.response.use((response) => {
//   const token = localStorage.getItem('token') ?? null;
//   if (token) config.headers['Authorization'] = `Bearer ${token}`;
//   return response;
// }, (error) => Promise.reject(error));

export default api;
