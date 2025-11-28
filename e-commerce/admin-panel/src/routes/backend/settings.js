const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const SettingController = require('../../controllers/SettingController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), SettingController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, SettingController.store);

module.exports = router;