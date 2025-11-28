const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const CouponController = require('../../controllers/CouponController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), CouponController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, CouponController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), CouponController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, CouponController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), CouponController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), CouponController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), CouponController.edit);

module.exports = router;