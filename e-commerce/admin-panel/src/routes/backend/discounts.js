const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const DiscountController = require('../../controllers/DiscountController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), DiscountController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, DiscountController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), DiscountController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, DiscountController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), DiscountController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), DiscountController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), DiscountController.edit);

module.exports = router;