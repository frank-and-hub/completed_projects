const { body, param, validationResult } = require(`express-validator`);
const ErrorLog = require(`@/models/errorlog`);

exports.validateUserSignUp = [
    body(`name`)
        .trim()
        .notEmpty()
        .withMessage(`Name is required`)
        .isLength({ min: 3, max: 20 })
        .withMessage(`Name must be at least 3 characters long`),
    body(`email`)
        .trim()
        .isEmail()
        .withMessage(`Invalid email address`)
        .normalizeEmail(),
    body(`password`)
        .trim()
        .isLength({ min: 6, max: 16 })
        .withMessage(`Password must be at least 6 characters long`),
    body(`phone`)
        .trim()
        .isLength({ min: 8, max: 13 })
        .isMobilePhone()
        .withMessage(`Invalid phone number`)
];

exports.validateUserSignin = [
    body(`email`)
        .trim()
        .isEmail().withMessage(`Invalid email address`)
        .normalizeEmail(),
    body(`password`)
        .trim()
        .notEmpty().withMessage(`Password is required`)
];

exports.validate = async (req, res, next) => {
    const errors = validationResult(req);
    let ip = req.headers[`x-forwarded-for`]?.split(`,`)[0] || req.socket.remoteAddress;

    if (ip.includes("::ffff:")) {
        ip = ip.split("::ffff:")[1];
    }

    if (!errors.isEmpty()) {
        const errorArray = errors.array();

        for (const error of errorArray) {
            const errorLog = new ErrorLog({
                route: req.originalUrl,
                statusCode: 422,
                errorMessage: error.msg,
                errorType: `Validation Error`,
                stackTrace: error.stack,
                IP: ip
            });
            await errorLog.save();
        }

        console.info(`Errors logged successfully`);
        return res.status(422).json({ errors: errorArray });
    }
    next();
}

exports.handleValidationErrors = (req, res, next) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }
    next();
};

exports.bannerCreateValidation = [
    body('name')
        .notEmpty()
        .withMessage('Name is required')
        .isLength({ min: 3 })
        .withMessage('Name must be at least 3 characters long'),
    body('title')
        .notEmpty()
        .withMessage('Title is required')
        .isLength({ min: 10 })
        .withMessage('Title must be at least 10 characters long'),
    body('url')
        .optional()
        .isURL()
        .withMessage('The URL must be a valid URL'),
    body('description')
        .notEmpty()
        .withMessage('Description is required')
        .isLength({ min: 20 })
        .withMessage('Description should be at least 20 characters long'),
    body('image')
        .custom((value, { req }) => {
            if (!req.file) {
                throw new Error('Image is required');
            }
            if (!['image/jpeg', 'image/png'].includes(req.file.mimetype)) {
                throw new Error('Image must be a JPEG or PNG');
            }
            return true;
        })
];

exports.bannerUpdateValidation = [
    param('id')
        .isMongoId()
        .withMessage('Invalid ID format'),
    body('name')
        .notEmpty()
        .withMessage('Name is required')
        .isLength({ min: 3 })
        .withMessage('Name must be at least 3 characters long'),
    body('title')
        .notEmpty()
        .withMessage('Title is required')
        .isLength({ min: 10 })
        .withMessage('Title must be at least 10 characters long'),
    body('url')
        .optional()
        .isURL()
        .withMessage('The URL must be a valid URL'),
    body('description')
        .notEmpty()
        .withMessage('Description is required')
        .isLength({ min: 20 })
        .withMessage('Description should be at least 20 characters long'),
    body('image')
        .custom((value, { req }) => {
            if (req.file && !['image/jpeg', 'image/png'].includes(req.file.mimetype)) {
                throw new Error('Image must be a JPEG or PNG');
            }
            return true;
        })
];