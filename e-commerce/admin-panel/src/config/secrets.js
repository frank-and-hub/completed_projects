require('dotenv').config();

const processed_env = process.env;

const connectionFunction = (processed_env) => {
    switch (processed_env.DB_TYPE) {
        case `localhost`:
            return `mongodb://localhost:27017/${processed_env.DB_NAME}`;
        case `live`:
            return `${processed_env.DB_URL}://${processed_env.CONNECTION_NAME}:${processed_env.DB_PWD}@${processed_env.CONNECTION_NAME}.${processed_env.DB_CONNECTION_BEFORE}${processed_env.DB_NAME}${processed_env.DB_CONNECTION_AFTER}=${processed_env.CONNECTION_NAME}`
        default:
            throw new Error(`Invalid DB_TYPE specified in the environment variables.`);
    }
}

module.exports = {
    nodeEnv: processed_env.NODE_ENV,
    secret: processed_env.SECRET || 'mySecretKey',
    jwtSecret: processed_env.JWT_SECRET,
    expiresin: processed_env.JWT_EXPIRES_IN,
    emailService: processed_env.EMAIL_SERVICE,
    emailUser: processed_env.EMAIL_USER,
    dbUrl: connectionFunction(processed_env),
    openApiKey: processed_env.OPEN_API_KEY,
}
