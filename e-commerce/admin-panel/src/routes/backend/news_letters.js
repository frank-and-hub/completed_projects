const express = require('express');
const router = express.Router();
const { checkAuth } = require('../../middleware/authMiddleware');
const NewsLetterController = require('../../controllers/NewsLetterController');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), NewsLetterController.index)
    .post(checkAuth, checkPermission(fileName, 'create'), NewsLetterController.store)

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), NewsLetterController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), NewsLetterController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), NewsLetterController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), NewsLetterController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), NewsLetterController.edit);

module.exports = router;