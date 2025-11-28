const express = require('express');
const router = express.Router();
const OrderController = require('../../controllers/OrderController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), OrderController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), OrderController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), OrderController.show)
    // .patch(checkAuth, checkPermission(fileName, 'edit'), OrderController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), OrderController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), OrderController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), OrderController.edit);

module.exports = router;