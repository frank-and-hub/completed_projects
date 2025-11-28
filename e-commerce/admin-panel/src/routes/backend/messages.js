const express = require('express');
const router = express.Router();
const { checkAuth } = require('../../middleware/authMiddleware');
const ChatController = require('../../controllers/ChatController');

// permissios check
const checkPermission = require('../../middleware/checkPermission');

// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, ChatController.index)
    .post(checkAuth, ChatController.store);

router.route('/:id')
    .get(checkAuth, ChatController.show)
    .patch(checkAuth, checkPermission(fileName, 'edit'), ChatController.update)
    .delete(checkAuth, checkPermission(fileName, 'delete'), ChatController.destroy);

// get form
router.route('/get/create').get(checkAuth, checkPermission(fileName, 'create'), ChatController.create);

// get edit details
router.route('/:id/edit').get(checkAuth, checkPermission(fileName, 'edit'), ChatController.edit);

// delete complete chat
router.route('/:id/chat').delete(checkAuth, checkPermission(fileName, 'delete'), ChatController.destroy_chat);

module.exports = router;