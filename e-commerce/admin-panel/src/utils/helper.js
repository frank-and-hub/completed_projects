const multer = require(`multer`);
const path = require(`path`);
const otpGenerator = require('otp-generator');
const transporter = require(`../config/email`);
const secrets = require(`../config/secrets`);

const storage = multer.diskStorage({
    destination: function (req, file, callback) {
        callback(null, `./public/file/`);
    },
    filename: function (req, file, callback) {
        const ext = path.extname(file.originalname);
        const fileName = new Date().toISOString().replace(/:/g, `-`) + ext;
        callback(null, fileName);
    }
});

const fileFilter = (allowedTypes) => (req, file, callback) => {
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes.test(file.mimetype);

    if (extname && mimetype) {
        return callback(null, true);
    } else {
        callback(new Error(`Invalid file type`), false);
    }
}

const fileImageFilter = fileFilter(/jpeg|jpg|png|pdf|svg|webp|jfif|pjpeg|pjp/);
const genericFileFilter = fileFilter(/pdf|png|pot|potm|ods|doc|docx|odt|xls|xlsx|ppt|pttx|txt|xml|pem/);

const fileLimit = { fileSize: 5 * 1024 * 1024 } // 50mb limit

const fileImageUpload = multer({
    storage: storage,
    limits: fileLimit,
    fileFilter: fileImageFilter
});

const fileUpload = multer({
    storage: storage,
    limits: fileLimit,
    fileFilter: genericFileFilter
});

const updateOps = (data) => {
    const array_data = data.body;
    const auth_user = data.userData.id;
    const update_ops = {}
    for (const ops of array_data) {
        update_ops[ops.propName] = ops.value;
    }
    update_ops[`updated_by`] = auth_user;
    return update_ops;
}

const requestTime = (req, res, next) => {
    req.requestTime = Date.now()
    next()
}

const sendEmail = async (to, subject, text) => {
    try {
        await transporter.sendMail({
            from: secrets.emailUser,
            to,
            subject,
            text
        });
        console.info(`Email sent successfully`);
    } catch (err) {
        console.info(`Error sending email:`, err.message);
    }
}

const filterData = async (item) => {
    return item?.map(per => (
        per._id
    ));
}

const phoneFormate = (string) => {
    return string?.trim()?.replace(`-`, ``)?.replace(`-`, ``)?.replace(` `, ``);
}

const loremPatterns = [
    /lorem ipsum/i,  // Match "Lorem ipsum" (case insensitive)
    /dolor sit amet/i,  // Match "dolor sit amet"
    /consectetur adipiscing elit/i,  // Match "consectetur adipiscing elit"
    /sed do eiusmod tempor incididunt/i,  // Match "sed do eiusmod tempor incididunt"
    /ut labore et dolore magna aliqua/i,  // Match "ut labore et dolore magna aliqua"
    /quis nostrud exercitation ullamco/i,  // Match "quis nostrud exercitation ullamco"
    /labore et dolore magna aliqua/i,  // Match "labore et dolore magna aliqua"
];

const makeSlug = (name) => name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');

const generateOtp = (length = 6) => {
    let otp = ``;
    const type = true;
    if (type){
        const digits = `0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`;
        for (let i = 0; i < length; i++) {
            otp += digits[Math.floor(Math.random() * digits.length)];
        }
    }else{
        otp =  otpGenerator.generate(length, { upperCase: false, specialChars: false });
    }
    return otp;
};

const generateDescription = (length = 100) =>{
    api_key = process.env.OPEN_API_KEY;
}

module.exports = { fileUpload, fileImageUpload, updateOps, requestTime, sendEmail, filterData, phoneFormate, loremPatterns, makeSlug, generateOtp, generateDescription }