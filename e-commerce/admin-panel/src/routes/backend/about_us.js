const express = require('express');
const router = express.Router();
const { checkAuth } = require('../../middleware/authMiddleware');
const AboutUsController = require('../../controllers/AboutUsController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), AboutUsController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), AboutUsController.store);

module.exports = router;