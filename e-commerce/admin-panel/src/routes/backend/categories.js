const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const CategoryController = require('../../controllers/CategoryController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), CategoryController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, CategoryController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), CategoryController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, CategoryController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), CategoryController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), CategoryController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), CategoryController.edit);

module.exports = router;