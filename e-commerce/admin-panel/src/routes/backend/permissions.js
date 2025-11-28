const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const PermissionController = require('../../controllers/PermissionController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), PermissionController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, PermissionController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), PermissionController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, PermissionController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), PermissionController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), PermissionController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), PermissionController.edit);

module.exports = router;