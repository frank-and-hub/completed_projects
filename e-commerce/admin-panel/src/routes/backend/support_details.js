const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const SupportDetailController = require('../../controllers/SupportDetailController');

// permissios check
const checkPermission = require('../../middleware/checkPermission');

// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), SupportDetailController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, SupportDetailController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), SupportDetailController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, SupportDetailController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), SupportDetailController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), SupportDetailController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), SupportDetailController.edit);

module.exports = router;