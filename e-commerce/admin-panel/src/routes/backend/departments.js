const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const DepartmentController = require('../../controllers/DepartmentController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), DepartmentController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, DepartmentController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), DepartmentController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, DepartmentController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), DepartmentController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), DepartmentController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), DepartmentController.edit);

module.exports = router;