const express = require('express');
const router = express.Router();

const FrontEndController = require('../../controllers/FrontEndController');

router.route('/:id').get(FrontEndController.show);

module.exports = router;