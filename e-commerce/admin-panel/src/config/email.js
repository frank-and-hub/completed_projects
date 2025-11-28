const nodemailer = require('nodemailer');
const secrets = require('../config/secrets');

const transporter = nodemailer.createTransport({
    service: secrets.emailService,
    auth: {
        user: secrets.emailUser,
        pass: secrets.emailPassword
    }
});

module.exports = transporter;