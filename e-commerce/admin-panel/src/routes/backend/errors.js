const express = require('express');
const router = express.Router();
const ErrorController = require('../../controllers/ErrorController');
const { checkAuth } = require('../../middleware/authMiddleware');
// permissios check
const checkPermission = require('../../middleware/checkPermission');
// get filea name
const fileName = __filename.slice(__dirname.length + 1).replace('.js', '');

router.route('/')
    .get(checkAuth, checkPermission(fileName, 'read'), ErrorController.index);

router.route('/:id')
    .get(checkAuth, checkPermission(fileName, 'read'), ErrorController.show)
    .delete(checkAuth, checkPermission(fileName, 'delete'), ErrorController.destroy);

module.exports = router;