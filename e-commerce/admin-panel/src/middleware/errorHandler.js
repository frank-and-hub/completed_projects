const config = require(`@/../nodemon`).env;
const ErrorLog = require(`@/models/errorlog`);

module.exports = async (err, req, res, next) => {
    try {
        const statusCode = err?.status || 500;
        const errorType = config.ERROR_CODES[statusCode] || `UnknownError`;

        let ip = req.headers[`x-forwarded-for`]?.split(`,`)[0] || req.socket.remoteAddress;

        if (ip.includes("::ffff:")) ip = ip.split("::ffff:")[1];

        const errorLog = new ErrorLog({
            route: req.originalUrl,
            statusCode: statusCode,
            errorMessage: err.message,
            errorType: errorType,
            stackTrace: err.stack,
            IP: ip
        });

        await errorLog.save()
            .then(() => {
                console.info(`Error logged successfully`);
            })
            .catch(logError => {
                console.error(`Failed to log error:`, logError);
            });

        res.status(statusCode).json({
            code: statusCode,
            message: err.message || `An error occurred`,
            errorType: errorType,
            line: err.line || `N/A`
        });

        console.log(err);

    } catch (logError) { next(logError) }
}
