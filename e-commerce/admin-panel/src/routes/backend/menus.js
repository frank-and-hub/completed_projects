const express = require('express');
const router = express.Router();

const validation = require('../../utils/validation');

const { checkAuth } = require('../../middleware/authMiddleware');
const MenuController = require('../../controllers/MenuController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), MenuController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), validation.handleValidationErrors, MenuController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), MenuController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), validation.handleValidationErrors, MenuController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), MenuController.destroy);

router.put('/:id/assign', checkAuth, checkPermission(fileName, 'edit'), MenuController.assignMenu);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), MenuController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), MenuController.edit);

module.exports = router;
