const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const WarrantyController = require('../../controllers/WarrantyController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), WarrantyController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, WarrantyController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), WarrantyController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, WarrantyController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), WarrantyController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), WarrantyController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), WarrantyController.edit);

module.exports = router;