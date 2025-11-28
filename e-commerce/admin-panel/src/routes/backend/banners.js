const express = require('express');
const router = express.Router();

// helpers
const helper = require('../../utils/helper');
const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const BannerController = require('../../controllers/BannerController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), BannerController.index)
    .post(checkAuth, helper.fileImageUpload.single('image'), checkPermission(fileName, 'create'), validation.handleValidationErrors, BannerController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), BannerController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, BannerController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), BannerController.destroy);

// update image
router.post('/:id/image', helper.fileImageUpload.single('image'), checkAuth, checkPermission(fileName, 'edit'), BannerController.image);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), BannerController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), BannerController.edit);

module.exports = router;