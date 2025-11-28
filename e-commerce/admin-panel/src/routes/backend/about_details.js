const express = require('express');
const router = express.Router();
// helpers
const helper = require('../../utils/helper');
const { checkAuth } = require('../../middleware/authMiddleware');
const AboutDetailController = require('../../controllers/AboutDetailController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), AboutDetailController.index)
    .post(helper.fileUpload.single('resume'), checkAuth, checkPermission(fileName, 'add'), AboutDetailController.update);

module.exports = router;