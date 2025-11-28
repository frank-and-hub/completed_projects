const express = require('express');
const router = express.Router();
const validation = require('../../utils/validation');
const { checkAuth } = require('../../middleware/authMiddleware');
const AuthController = require('../../controllers/AuthController');

router.route('/sign-in')
    .get(AuthController.getSignIn)
    .post(validation.validateUserSignin, validation.validate, AuthController.signIn);

router.route('/sign-up')
    .get(AuthController.getSignUp)
    .post(validation.validateUserSignUp, validation.validate, AuthController.signUp);

router.post('/sign-out', checkAuth, AuthController.signOut);

router.post('/send-otp', AuthController.sendOtp);
router.post('/verify-otp', AuthController.verifyOtp);

module.exports = router;