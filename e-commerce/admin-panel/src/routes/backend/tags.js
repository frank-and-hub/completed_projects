const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const TagController = require('../../controllers/TagController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), TagController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, TagController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), TagController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, TagController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), TagController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), TagController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), TagController.edit);

module.exports = router;