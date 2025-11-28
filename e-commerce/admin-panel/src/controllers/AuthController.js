const mongoose = require('mongoose');

const User = require('../models/user');
const Token = require('../models/token');
const VerifyOtp = require('../models/verify_otp');
const SocialDetail = require('../models/social_detail');

const AuthServices = require('../services/AuthServices');

const helper = require('../utils/helper');
const url = require('../config/url');

const RoleController = require('./RoleController');
const baseurl = `${url.apiUrl}`;
const unauthorized = `${process.env.UNAUTHORIZED}`;
const otp_not_found = `${process.env.OTP_NOT_FOUND}`;
const otp_expired = `${process.env.OTP_EXPIRED}`;
const otp_incorrect = `${process.env.OTP_INCORRECT}`;
const otp_verified = `${process.env.OTP_VERIFIED}`;
const error_code = `${process.env.ERROR_CODES}`;
const status_active = `${process.env.STATUS_ACTIVE}`;

exports.getSignIn = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Sign in form`,
            body: {
                'email': 'String',
                'password': 'String',
            }
        });
    } catch (err) { next(err) }
}

exports.signIn = async (req, res, next) => {
    try {
        const { email, password } = req.body;
        console.log(req.body);
        // fetch user data
        const userData = await User.findOne({ email: email, status: status_active })
            .select('_id name email phone password password_text role image gender address about city state zipcode terms status updated_by')
            .populate('role', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path');

        // check user data
        if (!userData) return res.status(401).json({ message: `User not found!` });

        // check password validation
        const isPasswordValid = await AuthServices.comparePasswords(password, userData.password);

        // check password
        const checkPasswordMatch = (password === userData.password_text);

        if (!isPasswordValid && !checkPasswordMatch) return res.status(401).json({ message: unauthorized });

        // generate token
        const token = await AuthServices.generateToken(userData);

        // get login user ip address
        let ip = req.headers['x-forwarded-for']?.split(',')[0] || req.socket.remoteAddress;
        if (ip.includes("::ffff:")) {
            ip = ip.split("::ffff:")[1];
        }

        // generate a new token 
        const newToken = await Token({
            _id: new mongoose.Types.ObjectId(),
            user: userData?._id,
            token: token,
            loginTime: new Date().toISOString(),
            ip_address: ip,
        });

        // store new token
        const tokenResult = await newToken.save();

        const social_details = await SocialDetail.find({ 'user': userData?._id })
            .select('_id name url icon')
            .sort({ _id: -1 });

        const userWithDetails = {
            ...userData.toObject(),
            social_details,
        };

        // update token into user schema
        const result = await User.updateOne({ _id: userData._id }, { $push: { token: tokenResult?._id } });

        // conferm modification
        if (result.modifiedCount === 0 || !token) return res.status(401).json({ message: unauthorized });

        // result
        return res.status(200).json({ message: `User sign-in 👍`, user: userWithDetails, token: token });
    } catch (err) { next(err) }
}

exports.getSignUp = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Sign up form`,
            body: {
                'name': 'String',
                'phone': 'Number',
                'email': 'String',
                'password': 'String'
            }
        });
    } catch (err) { next(err) }
}

exports.signUp = async (req, res, next) => {
    const { name, phone, email, password, terms } = req.body;
    try {
        const existsUser = await User.findOne({ email: email });
        if (existsUser) return res.status(409).json({ message: `User already exists` });

        const roleResponse = await RoleController.getGuestRole('guest-user');
        const guestRoleId = roleResponse?.roleId ?? roleResponse;

        const hashPassword = await AuthServices.hashPassword(password);

        const user = new User({
            _id: new mongoose.Types.ObjectId(),
            name,
            phone,
            email,
            password: hashPassword,
            password_text: password,
            terms,
            role: guestRoleId
        });
        await user.save();

        res.status(201).json({ message: `Successfully sign up 👍`, request: { method: 'GET', url: `${baseurl}/sign-in/` } });
    } catch (err) { next(err) }
}

exports.signOut = async (req, res, next) => {
    let userId = req?.userData?.id;
    try {
        const authHeader = req.headers.authorization;
        if (!authHeader || !authHeader.startsWith('Bearer ')) return res.status(401).json({ message: 'JWT must be provided' });

        const token = authHeader.split(' ')[1];

        const tokenData = await Token.findOne({ user: userId, token: token }).select('_id user token');
        if (!tokenData) return res.status(401).json({ message: 'Invalid token' });

        const deletedToken = await Token.deleteOne({ _id: tokenData?._id });

        if (deletedToken.deletedCount === 0) return res.status(400).json({ message: 'Failed to delete token' });

        const user = await User.findOne({ _id: userId });
        if (!user) return res.status(401).json({ message: 'Unauthorized User' });

        await User.updateOne({ _id: user._id }, { $pull: { token: tokenData?._id } });
        res.status(200).json({ message: 'Successfully logged out 👍', status: 200 });
    } catch (err) {
        next(err);
    }
}

exports.verifyOtp = async (req, res, next) => {
    try {
        const { input, otp, type } = req.body;
        // get login user ip address
        let ip = req.headers['x-forwarded-for']?.split(',')[0] || req.socket.remoteAddress;
        if (ip.includes("::ffff:")) {
            ip = ip.split("::ffff:")[1];
        }

        // fetch user data
        let query = { status: status_active };

        switch (type) {
            case 'email': query.email = input; break;
            case 'phone': query.phone = input; break;
            default: return res.status(400).json({ message: 'Invalid type. Must be "email" or "phone".' });
        }

        const userData = await User.findOne(query)
            .select('_id name email phone password password_text role image gender address about city state zipcode terms status updated_by')
            .populate('role', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path');

        // check user data
        if (!userData) return res.status(401).json({ message: `User not found!` });

        const authOtp = await VerifyOtp.findOne({ user: userData?.id, status: 'pending', verifyType: 'general', type: type});

        if (!authOtp) return res.status(401).json({ message: otp_not_found });

        if (new Date() > new Date(authOtp.expired_at)) {
            VerifyOtp.updateOne({ _id: authOtp._id }, {
                $set: {
                    status: 'expired',
                    verified_at: new Date()
                }
            });
            return res.status(401).json({ message: otp_expired });
        }

        if (authOtp.otp !== otp) return res.status(401).json({ message: otp_incorrect });

        await VerifyOtp.updateOne({ _id: authOtp._id }, {
            $set: {
                status: 'verified',
                verified_at: new Date()
            }
        });

        // generate token
        const token = await AuthServices.generateToken(userData);

        // generate a new token 
        const newToken = await Token({
            _id: new mongoose.Types.ObjectId(),
            user: userData?._id,
            token: token,
            loginTime: new Date().toISOString(),
            ip_address: ip,
        });

        // store new token
        const tokenResult = await newToken.save();

        const social_details = await SocialDetail.find({ 'user': userData?._id })
            .select('_id name url icon')
            .sort({ _id: -1 });

        const userWithDetails = {
            ...userData.toObject(),
            social_details,
        };

        // update token into user schema
        const result = await User.updateOne({ _id: userData._id }, { $push: { token: tokenResult?._id } });

        // conferm modification
        if (result.modifiedCount === 0 || !token) return res.status(401).json({ message: unauthorized });

        // result
        return res.status(200).json({ message: `User sign-in 👍`, user: userWithDetails, token: token });
    } catch (err) { next(err) }
}

exports.resetOtp = async (req, res, next) => {
    try {
        const { email = null, phone = null, otp, type, verifyType = 'general' } = req.body;

        // fetch user data
        let query = { status: status_active };

        switch (type) {
            case 'email': query.email = email; break;
            case 'phone': query.phone = phone; break;
            default: return res.status(400).json({ message: 'Invalid type. Must be "email" or "phone".' });
        }

        const userData = await User.findOne(query)
            .select('_id name email phone password password_text role')
            .populate('role', '_id name');

        // check user data
        if (!userData) return res.status(401).json({ message: `User not found!` });

        // Expire existing OTPs if any
        await VerifyOtp.updateMany({ user: userData?.id, status: 'pending', verifyType: type }, {
            $set: {
                status: 'expired',
                verified_at: new Date()
            }
        });

        // Generate new OTP
        const newOtp = new VerifyOtp({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            otp: helper.generateOtp(),
            generated_at: new Date(),
            expired_at: new Date(Date.now() + 5 * 60 * 1000),
            type: type,
            verifyType: verifyType,
            status: 'pending'
        });
        const savedOtp = await newOtp.save();

        return res.status(200).json({ message: `Otp sendet succeffully`, otp: savedOtp.otp });
    } catch (err) { next(err) }
}

exports.sendOtp = async (req, res, next) => {
    try {
        const { email: identifier, password } = req.body;
        console.log(req.body);
        // Utility regex patterns
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(identifier);
        const isPhone = /^\d{10,15}$/.test(identifier); // adjust for your expected phone format

        let query = { status: status_active };

        if (isEmail) {
            query.email = identifier;
        } else if (isPhone) {
            query.phone = identifier;
        } else {
            return res.status(400).json({ message: 'Invalid email or phone format.' });
        }
        console.log(query);
        // fetch user data
        const userData = await User.findOne(query)
            .select('_id name email phone password password_text role image gender address about city state zipcode terms status updated_by')
            .populate('role', '_id name');

        // check user data
        if (!userData) return res.status(401).json({ message: `User not found!` });

        // check password validation
        const isPasswordValid = await AuthServices.comparePasswords(password, userData.password);

        // check password
        const checkPasswordMatch = (password === userData.password_text);

        if (!isPasswordValid && !checkPasswordMatch) return res.status(401).json({ message: unauthorized });

        const type = isEmail ? 'email' : (isPhone ? 'phone' : 'other');
        const otp = helper.generateOtp();

        await VerifyOtp.deleteMany({ user: userData._id, type: type, verifyType: 'general', status: 'pending' });

        // Generate new OTP
        const newOtp = new VerifyOtp({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            otp: otp,
            generated_at: new Date(),
            expired_at: new Date(Date.now() + 5 * 60 * 1000),
            type: type,
            verifyType: 'general',
            status: 'pending'
        });
        
        await newOtp.save();

        // result
        return res.status(200).json({ message: `Otp sent to your ${isEmail ? 'email address' : 'phone number'} 👍`, user: userData, type: type , otp: otp});
    } catch (err) { next(err) }
}
