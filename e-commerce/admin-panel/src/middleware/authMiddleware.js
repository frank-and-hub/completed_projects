const jwt = require('jsonwebtoken');
const secrets = require('../config/secrets');

module.exports.checkAuth = async (req, res, next) => {
    try {
        const authHeader = req.headers.authorization;
        if (!authHeader || !authHeader.startsWith('Bearer ')) return res.status(401).json({ message: 'JWT must be provided' });

        const token = authHeader.split(' ')[1];

        jwt.verify(token, secrets.jwtSecret, async (err, decoded) => {
            if (err) {
                switch (err.name) {
                    case 'TokenExpiredError':
                        return res.status(401).json({ message: 'Token expired' });
                    case 'JsonWebTokenError':
                        return res.status(401).json({ message: 'Invalid token' });
                    case 'NotBeforeError':
                        return res.status(401).json({ message: 'Token not active yet' });
                    default:
                        return res.status(500).json({ message: 'Authorization failed due to internal error' });
                }
            }
            req.userData = decoded;
            next()
        });

    } catch (err) { next(err)  }
}

module.exports.restrictTo = (...roles) => {
    return (req, res, next) => {
        if (!roles.includes(req.user.role)) {
            return res.status(403).json({ message: "Access denied" });
        }
        next();
    };
};