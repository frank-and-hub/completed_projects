const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const ColorController = require('../../controllers/ColorController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), ColorController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, ColorController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), ColorController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, ColorController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), ColorController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), ColorController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), ColorController.edit);

module.exports = router;