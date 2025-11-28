const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const WarehouseController = require('../../controllers/WarehouseController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), WarehouseController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, WarehouseController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), WarehouseController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, WarehouseController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), WarehouseController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), WarehouseController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), WarehouseController.edit);

module.exports = router;