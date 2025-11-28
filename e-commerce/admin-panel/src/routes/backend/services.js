const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const ServiceController = require('../../controllers/ServiceController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), ServiceController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, ServiceController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), ServiceController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, ServiceController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), ServiceController.destroy);

router.put('/user/:userId', checkAuth, ServiceController.user_services);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), ServiceController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), ServiceController.edit);

module.exports = router;