const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const TermsAndConditionsController = require('../../controllers/TermsAndConditionsController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), TermsAndConditionsController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, TermsAndConditionsController.store);

module.exports = router;