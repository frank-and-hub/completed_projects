const express = require('express');
const router = express.Router();
const { checkAuth } = require('../../middleware/authMiddleware');
const SocialDetailController = require('../../controllers/SocialDetailController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), SocialDetailController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), SocialDetailController.store);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), SocialDetailController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), SocialDetailController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), SocialDetailController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), SocialDetailController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), SocialDetailController.edit);

module.exports = router;