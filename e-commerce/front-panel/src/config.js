
const config = {
       baseUrl: `${process.env.REACT_APP_BASE_URL}`,
       //  baseUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/`,
       reactUrl: `${process.env.REACT_APP_BASE_URL}:${process.env.REACT_APP_API_PORT}`,
       //  reactUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/`,
       reactApiUrl: `${process.env.REACT_APP_BASE_URL}:${process.env.REACT_APP_API_PORT}/api`,
       //  reactApiUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/api`,
       pageignation: `${process.env.REACT_APP_PAGEIGNATION}`
};

export default config;