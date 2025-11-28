const express = require('express');
const router = express.Router();
// helpers
const helper = require('../../utils/helper');
const { checkAuth } = require('../../middleware/authMiddleware');
const PageController = require('../../controllers/PageController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), PageController.index)
    .post(helper.fileUpload.single('resume'), checkAuth, checkPermission(fileName, 'add'), PageController.store_or_update);

module.exports = router;