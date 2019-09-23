const express = require('express');
const router = express.Router();

const SurveysController = require('./../controllers/surveys-controller');

router.post('/notifyStart', SurveysController.StartSurveyNotify);

module.exports = router;