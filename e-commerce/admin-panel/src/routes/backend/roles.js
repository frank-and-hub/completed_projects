const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const RoleController = require('../../controllers/RoleController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), RoleController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, RoleController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), RoleController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, RoleController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), RoleController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), RoleController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), RoleController.edit);

module.exports = router;