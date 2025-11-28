const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const StoreController = require('../../controllers/StoreController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), StoreController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, StoreController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), StoreController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, StoreController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), StoreController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), StoreController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), StoreController.edit);

module.exports = router;