const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const DesignationController = require('../../controllers/DesignationController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), DesignationController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, DesignationController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), DesignationController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, DesignationController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), DesignationController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), DesignationController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), DesignationController.edit);

module.exports = router;