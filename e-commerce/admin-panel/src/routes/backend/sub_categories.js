const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const SubCategoryController = require('../../controllers/SubCategoryController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), SubCategoryController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, SubCategoryController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), SubCategoryController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, SubCategoryController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), SubCategoryController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), SubCategoryController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), SubCategoryController.edit);

module.exports = router;