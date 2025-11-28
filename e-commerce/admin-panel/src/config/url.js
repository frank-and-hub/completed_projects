require('dotenv').config();

module.exports = {
    port: `${process.env.PORT}`,
    host: `${process.env.HOST}`,
    // host: `https://f7tvdqzn-5080.inc1.devtunnels.ms/`,
    apiUrl: `${process.env.BASE_URL}:${process.env.PORT}/${process.env.API}`,
    // apiUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/api`,
    apiBaseUrl: `${process.env.BASE_URL}:${process.env.PORT}`,
    // apiBaseUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/`,
    reactBaseUrl: `${process.env.BASE_URL}:${process.env.REACT_PORT}`,
    // reactBaseUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/`,
    reactApiUrl: `${process.env.BASE_URL}:${process.env.REACT_PORT}/${process.env.API}`,
    // reactApiUrl: `https://f7tvdqzn-5080.inc1.devtunnels.ms/api`,
}