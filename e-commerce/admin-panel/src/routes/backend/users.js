const express = require('express');
const router = express.Router();

// helpers
const helper = require('../../utils/helper');
const validation = require('../../utils/validation');

// controllers
const UserController = require('../../controllers/UserController');

// Middleware
const { checkAuth } = require('../../middleware/authMiddleware');

// permissions check
const checkPermission = require('../../middleware/checkPermission');

// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkPermission(fileName, 'read'), UserController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, UserController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), UserController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, UserController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), UserController.destroy);

router.put('/:id/role', checkAuth, checkPermission(fileName, 'edit'), UserController.assignRole);

router.post('/:id/image', helper.fileImageUpload.single('image'), checkAuth, checkPermission(fileName, 'edit'), UserController.profile);

router.post('/verify/password', checkAuth, checkPermission(fileName, 'edit'), UserController.verifyPassword);

router.post('/change/password', checkAuth, checkPermission(fileName, 'edit'), UserController.changePassword);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), UserController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), UserController.edit);

// get user permssion
router.route('/:id/permssions')
    .get(checkAuth, checkPermission(fileName, 'edit'), UserController.permssion)
    .post(checkAuth, checkPermission(fileName, 'edit'), UserController.update_permssion);

module.exports = router;
