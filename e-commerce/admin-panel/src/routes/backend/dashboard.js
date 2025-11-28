const express = require('express');
const router = express.Router();
const DasboardController = require('../../controllers/DasboardController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/').post(checkAuth, checkPermission(fileName, 'read'), DasboardController.index)

module.exports = router;