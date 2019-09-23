const express = require('express');
const router = express.Router();

const BillingController = require('./../controllers/billing-controller');

router.post('/enableAutomaticBilling', BillingController.automaticBillingEnabled);
router.post('/invoiceCreated', BillingController.invoiceCreated);

module.exports = router;