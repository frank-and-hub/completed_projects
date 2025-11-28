const express = require('express');
const router = express.Router();
const { checkAuth } = require('../../middleware/authMiddleware');
const FaqController = require('../../controllers/FaqController');

// permissios check
const checkPermission = require('../../middleware/checkPermission');

// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, FaqController.index)
    .post(checkAuth, FaqController.store);

router.route('/:id')
    .get(checkAuth, FaqController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), FaqController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), FaqController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), FaqController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), FaqController.edit);

module.exports = router;