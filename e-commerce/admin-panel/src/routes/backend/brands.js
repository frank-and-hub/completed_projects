const express = require('express');
const router = express.Router();

// helpers
const helper = require('../../utils/helper');
const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const BrandController = require('../../controllers/BrandController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), BrandController.index)
    .post(checkAuth, helper.fileImageUpload.single('image'), checkPermission(fileName, 'create'), validation.handleValidationErrors, BrandController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), BrandController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, BrandController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), BrandController.destroy);

// update image
router.post('/:id/image', helper.fileImageUpload.single('image'), checkAuth, checkPermission(fileName, 'edit'), BrandController.image);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), BrandController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), BrandController.edit);

module.exports = router;