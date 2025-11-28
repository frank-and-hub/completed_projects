const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const WishListController = require('../../controllers/WishListController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), WishListController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, WishListController.store);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), WishListController.create);

module.exports = router;